$("backToPreviousPage").addEventListener("click", () => {
    window.history.go(-1);
});

async function GetSubmissions() {
    let params = new URLSearchParams(window.location.search);
    let contentId = params.get('id');

    try {
        let [response, result] = await API.getSubmissions(contentId);
        if (response.status == 401) {
            window.location.href = './login.html';
        }
        else if (response.status == 403) {
            location.href = './kurzusok.html';
        }
        console.log(result);
        showSubedData(result);
    } catch (error) {
        console.log(error);
    }
}

function showSubedData(adatok) {
    let ki = document.querySelector(".content");

    for (let adat of adatok) {
        let dataDiv = create('div', 'dataDiv');

        let div = create('div');
        div.style.display = 'flex';
        div.style.alignItems = 'center';
        div.style.gap = "15px";

        let h1 = create('h2');
        h1.innerHTML = adat.lastname + " " + adat.firstname;

        let div2 = create('div', 'open');
        div2.addEventListener('click', (event) => toggleArrow(event, adat.submission_id)); 

        let svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
        svg.classList.add("arrow");
        svg.setAttribute("xmlns", "http://www.w3.org/2000/svg");
        svg.setAttribute("fill", "none");
        svg.setAttribute("viewBox", "0 0 24 24");
        svg.setAttribute("stroke-width", "1");
        svg.setAttribute("stroke", "currentColor");
        svg.classList.add("size-6");

        let path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        path.setAttribute("stroke-linecap", "round");
        path.setAttribute("stroke-linejoin", "round");
        path.setAttribute("d", "m19.5 8.25-7.5 7.5-7.5-7.5");

        let datas = create('div', 'datasDiv');
        datas.id = `datasDiv-${adat.submission_id}`;
        datas.style.display =  "none";

        let details = create('div','details')

        details.innerHTML = "<strong>Beadva: </strong>" + adat.submitted + "<br>"
        details.innerHTML += "<strong>Beadott fájlok száma: </strong>" + adat.files_count + "<br>"
        
        if (adat.files_count > 0) {
            details.innerHTML += "<strong>Fájlok: </strong>"
            
            let filesList = create('div');
            filesList.id = `filesList-${adat.submission_id}`;
            datas.appendChild(details)
            datas.appendChild(filesList);
        }

        let hiddenSubId = create('input');
        hiddenSubId.type = "number"
        hiddenSubId.value = adat.submission_id
        hiddenSubId.id = "subId"
        hiddenSubId.hidden = true

        div.appendChild(h1);
        div.appendChild(div2);
        div2.append(svg);
        svg.appendChild(path);

        dataDiv.appendChild(div);
        dataDiv.appendChild(datas)
        dataDiv.appendChild(hiddenSubId)

        if (adat.max_points != null) {
            let p = create('p');
            p.innerHTML = `Értékelés: ${adat.rating ?? '-'} / ${adat.max_points} p (${Math.round(adat.rating / adat.max_points * 100)}%)`;

            let modifyDiv = create('div');

            let modifyPointsInput = create('input');
            modifyPointsInput.id = `modifyPointsInput-${adat.submission_id}`;
            modifyPointsInput.type = 'number';
            modifyPointsInput.min = 0;
            modifyPointsInput.max = adat.max_points;

            let modifyPointsBtn = create('button', 'rateButton');
            modifyPointsBtn.innerHTML = 'Értékelés';
            modifyPointsBtn.addEventListener('click', () => {
                let points = parseInt($(`modifyPointsInput-${adat.submission_id}`).value);
                if (isNaN(points) || points > adat.max_points || points < 0) {
                    showModal("A megadott pontszám érvénytelen!");
                    return;
                }
                rate(adat.submission_id, points);
            });

            datas.appendChild(p);
            modifyDiv.appendChild(modifyPointsInput);
            modifyDiv.appendChild(modifyPointsBtn);
            datas.appendChild(modifyDiv);
        }
        ki.appendChild(dataDiv)
    }
}

function toggleArrow(event, submissionId) {
    let arrow = event.currentTarget.querySelector('.arrow');
    let previewDiv = $(`datasDiv-${submissionId}`);

    if (arrow) {
        arrow.classList.toggle('flipped');
    }

    if (previewDiv.style.display === "none" || previewDiv.style.display === "") {
        previewDiv.style.display = "flex";
        setTimeout(() => previewDiv.classList.add("active"),5);
        GetSubmittedFiles(submissionId);
    } else {
        previewDiv.classList.remove("active");
        setTimeout(() => (previewDiv.style.display = "none"), 300);
    }
}

async function GetSubmittedFiles(submissionId) {
    console.log("Lekérendő Submission ID:", submissionId);

    try {
        let [response, result] = await API.getSubmissionFiles(submissionId);

        console.log(result);
        showFiles(result, submissionId);
    } catch (error) {
        console.log(error);
    }
}

function showFiles(files, submissionId) {
    let ki = document.querySelector(`#filesList-${submissionId}`);
    
    if (ki.querySelectorAll(".fileDiv").length > 0) {
        console.log("Fájlok már megvannak jelenítve.");
        return;
    }

    if (files.length == 0) {
        return;
    }

    for (let file of files) {
        let fileDiv = create('div', 'fileDiv');
        fileDiv.id = "fileDiv" + file.file_id;

        fileDiv.addEventListener('click', () => {
            window.location.href = `downloader?file_id=${file.file_id}&attached_to=submission&id=${submissionId}`;
        });

        let svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
        svg.setAttribute("xmlns", "http://www.w3.org/2000/svg");
        svg.setAttribute("fill", "none");
        svg.setAttribute("viewBox", "0 0 24 24");
        svg.setAttribute("stroke-width", "1.5");
        svg.setAttribute("stroke", "currentColor");
        svg.classList.add("size-6");

        let path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        path.setAttribute("stroke-linecap", "round");
        path.setAttribute("stroke-linejoin", "round");
        path.setAttribute("d", "M3.75 6.75h16.5M3.75 12H12m-8.25 5.25h16.5");

        let h1 = create('h1', 'fileName');
        h1.innerHTML = file.name;

        fileDiv.appendChild(svg);
        svg.appendChild(path);
        fileDiv.appendChild(h1);
        ki.appendChild(fileDiv);
    }
}

async function rate(submission_id, points) {
    try {
        let [response, result] = await API.rateSubmission(submission_id, points);

        if (response.ok) {
            showModal('Sikeres értékelés!', () => location.reload());
        }
        else {
            showModal('Hiba történt az értékelés közben!');
        }
    }
    catch (e) {
        console.error(e);
    }
}

function showModal(text, action = () => {}) {
    let modal = create('div', 'modal');
    let alertDiv = $('alertDiv');
    alertDiv.style.display = 'block';
    alertDiv.appendChild(modal);

    let modal_content = create('div', 'modal-content');
    modal.appendChild(modal_content);

    let p = create('p');
    modal_content.appendChild(p);
    p.innerHTML = text;

    let ok_button = create('button');
    modal_content.appendChild(ok_button);
    ok_button.innerHTML = 'OK';

    ok_button.addEventListener('click', () => {
        alertDiv.style.display = 'none';
        alertDiv.innerHTML = '';
        action();
    });
    ok_button.focus();
}

window.addEventListener("load", GetSubmissions);