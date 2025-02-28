let contentType = $("typeContent")
contentType.addEventListener("click", ()=>{
    let selectElement = document.querySelector('select');
    let selectedOption = selectElement.options[selectElement.selectedIndex];
    let type = selectedOption.value;
    if(type == "feladat"){
        $("feladatDiv").style.display = "flex"
        $("feladatDiv").style.flexDirection = "column"
        $("tananyagDiv").style.display = "none"
    }
    if(type == "tananyag"){
        $("tananyagDiv").style.display = "flex"
        $("tananyagDiv").style.flexDirection = "column"
        $("feladatDiv").style.display = "none"
    }
    else{
        $("tananyagDiv").style.display = "none"
    }
})

let pageLink = $("backtoPage").addEventListener("click", ()=>{
    history.back()
})

async function contentPublish(data) {
    try {
        let urlParams = getUrlParams();
        if (!urlParams.has('id')) {
            alert('Hiba!');
            return;
        }

        let courseId = parseInt(urlParams.get('id'));

        let reqData = {
            course_id: courseId,
            ...data
        };

        let response = await fetch('../api/content/create', {
            method: 'POST',
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(reqData)
        });

        // TODO
        let result = await response.json();

        console.log(result);
        if (response.ok) {
            window.location.href = "./" + courseId
        }
    }
    catch (e) {
        console.error(e);
        alert("Hiba történt a kérés feldolgozás közben. Kérjük próbálja meg újra később!");
    }
}

// Feladat
async function onNewTask(e) {
    e.preventDefault();
    let title = $('fcim').value;
    let desc = $('fleiras').value;
    // Az input mező értéke helyi (UTC+1) időben van. Az időzóna nincs tárolva.
    let localTime = $('fhatarido').value;
    // Konvertálás UTC 0 időzónás dátumra (Z betű a végén). (ISO 8601 formátum)
    let due = new Date(localTime).toJSON();
    let points = parseInt($('fpont').value);
    let file = $('ffile').files[0];

    if (title == '') {
        alert("A cím megadása kötelező!");
        return;
    }

    let data = {
        title,
        desc,
        task: true,
        deadline: due,
        maxpoint: points,
        file
    };

    await contentPublish(data);
}

// Tananyag
async function onNewMaterial(e) {
    e.preventDefault();
    let title = $('tcim').value;
    let desc = $('tleiras').value;
    let file = $('tfile').files[0];

    if (title == '') {
        alert("A cím megadása kötelező!");
        return;
    }

    let data = {
        title,
        desc,
        task: false,
        file
    };

    await contentPublish(data);
}

$('ujFeladatForm').addEventListener('submit', async (e) => onNewTask(e));
$('ujTananyagForm').addEventListener('submit', async (e) => onNewMaterial(e));
window.addEventListener('load', () => {
    let urlParams = getUrlParams();
    if (!urlParams.has('id')) {
        location.href = '../kurzusok.html';
    }
})