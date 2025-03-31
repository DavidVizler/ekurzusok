document.getElementById("backToPreviousPage").addEventListener("click", () => {
    window.history.go(-1);
});

async function GetSubmissions() {
    let params = new URLSearchParams(window.location.search);
    let tartalomId = params.get('id');
    console.log(tartalomId);
    let reqData = {
        "content_id": parseInt(tartalomId)
    };

    try {
        let request = await fetch('api/query/submissions', {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(reqData)
        });
        let response = await request.json();
        console.log(response);
        showSubedData(response);
    } catch (error) {
        console.log(error);
    }
}

function showSubedData(adatok) {
    let ki = document.querySelector(".content");

    for (let adat of adatok) {
        let dataDiv = document.createElement("div")
        dataDiv.classList.add("dataDiv")

        let div = document.createElement("div");
        div.style.display = 'flex';
        div.style.alignItems = 'center';
        div.style.gap = "15px";

        let h1 = document.createElement("h2");
        h1.innerHTML = adat.lastname + " " + adat.firstname;

        let div2 = document.createElement("div");
        div2.classList.add("open");
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
        path.setAttribute("d", "m4.5 18.75 7.5-7.5 7.5 7.5");

        let path2 = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        path2.setAttribute("stroke-linecap", "round");
        path2.setAttribute("stroke-linejoin", "round");
        path2.setAttribute("d", "m4.5 12.75 7.5-7.5 7.5 7.5");

        let datas = document.createElement("div")
        datas.id = `datasDiv-${adat.submission_id}`;
        datas.style.display =  "none"
        datas.classList.add("datasDiv")

        datas.innerHTML = "Beadva: " + adat.submitted + "<br>"
        datas.innerHTML += "Beadott fájlok száma: " + adat.files_count + "<br>"
        datas.innerHTML += "Fájlok: "

        let hiddenSubId = document.createElement("input")
        hiddenSubId.type = "number"
        hiddenSubId.value = adat.submission_id
        hiddenSubId.id = "subId"
        hiddenSubId.hidden = true

        div.appendChild(h1);
        div.appendChild(div2);
        div2.append(svg);
        svg.appendChild(path);
        svg.appendChild(path2);

        dataDiv.appendChild(div);
        dataDiv.appendChild(datas)
        dataDiv.appendChild(hiddenSubId)

        let hr = document.createElement("hr");
        dataDiv.appendChild(hr);

        ki.appendChild(dataDiv)
    }
}

function toggleArrow(event, submissionId) {
    let arrow = event.currentTarget.querySelector('.arrow');
    let previewDiv = document.getElementById(`datasDiv-${submissionId}`);

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
        let request = await fetch("api/query/submission-files", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ "submission_id": submissionId })
        });

        let response = await request.json();
        console.log(response);
        showFiles(response, submissionId);
    } catch (error) {
        console.log(error);
    }
}

function showFiles(files, submissionId) {
    let ki = document.querySelector(`#datasDiv-${submissionId}`);
    
    if (ki.querySelectorAll(".fileDiv").length > 0) {
        console.log("Fájlok már megvannak jelenítve.");
        return;
    }

    for (let file of files) {
        let fileDiv = document.createElement("div");
        fileDiv.classList.add("fileDiv");
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

        let h1 = document.createElement("h1");
        h1.classList.add("fileName");
        h1.innerHTML = file.name;

        fileDiv.appendChild(svg);
        svg.appendChild(path);
        fileDiv.appendChild(h1);
        ki.appendChild(fileDiv);
    }
}

window.addEventListener("load", GetSubmissions);