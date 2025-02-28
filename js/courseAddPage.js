let design;

// Fetch importálás, hogy Firefoxban is megjelenjen a design
async function getDesignJson() {
    let response = await fetch("./js/design.json");
    design = await response.json();
    designOptionLoad();
    loadDefaultCard();
}

function designOptionLoad() {
    let select = $("DesignSelect");
    design.forEach(elem => {
        let option = create("option");
        option.value = elem.designId;
        option.text = elem.designName;
        select.appendChild(option);
    });

    select.value = 1;
}

function loadDefaultCard() {
    let preview = $("preView");
    preview.innerHTML = "";

    let defaultDesign = design.find(elem => elem.designName === "Földrajz");

    if (defaultDesign) {
        let card = create("div", "card");

        let cardHeader = create("div", "card-header");

        let img = create("img", "card-img-top");
        img.src = defaultDesign.image;
        img.alt = "Kurzus témája";

        let cardBody = create("div", "card-body");

        let cardTitle = create("h5", "card-title");
        cardTitle.textContent = "Kurzus neve";

        cardBody.appendChild(cardTitle);
        cardHeader.appendChild(img);
        card.appendChild(cardHeader);
        card.appendChild(cardBody);
        preview.appendChild(card);

        preview.style.display = "flex"; 
        preview.style.justifyContent = "center";
        preview.style.alignItems = "center";
    }
}

function previewLoad() {
    let selectDesign = document.querySelector("#DesignSelect");
    let designName = selectDesign.options[selectDesign.selectedIndex].text;
    let preview = $("preView");
    let kurzusNev = $("KurzusNev");
    preview.innerHTML = ""; // Clear default card

    design.forEach(elem => {
        if (elem.designName == designName) {
            preview.style.display = "flex";
            preview.style.justifyContent = "center";
            preview.style.alignItems = "center";

            let card = create("div", "card");

            let cardHeader = create("div", "card-header");

            let img = create("img", "card-img-top");
            img.src = elem.image;
            img.alt = "Kurzus témája";

            let cardBody = create("div");
            cardBody.classList.add("card-body");

            let cardTitle = create("h5", "card-title");
            cardTitle.textContent = kurzusNev.value || "Kurzus neve";

            cardBody.appendChild(cardTitle);
            cardHeader.appendChild(img);
            card.appendChild(cardHeader);
            card.appendChild(cardBody);
            preview.appendChild(card);
        }
    });
}

async function sendNewCourseData() {
    let name = $("KurzusNev").value;
    let desc = $("Leiras").value;
    let courseDesgin = parseInt($("DesignSelect").value);

    let newCourseData = {
        "name": name,
        "desc": desc,
        "design": courseDesgin
    };

    let request = await fetch("./api/course/create", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify(newCourseData)
    });

    if (request.ok) {
        window.open("./kurzusok.html", "_self");
    }
}

window.addEventListener('load', getDesignJson);
window.addEventListener('load', () => {
    $('newCourseForm').addEventListener('submit', (e) => {
        sendNewCourseData();
        e.preventDefault();
    });
});

$("DesignSelect").addEventListener('change', previewLoad);

$("closeButton").addEventListener('click', () => {
    window.open("./kurzusok.html", "_self");
});