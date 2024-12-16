let contentType = document.getElementById("typeContent")
contentType.addEventListener("click", ()=>{
    let selectElement = document.querySelector('select');
    let selectedOption = selectElement.options[selectElement.selectedIndex];
    let type = selectedOption.value;
    if(type == "feladat"){
        document.getElementById("feladatDiv").style.display = "flex"
        document.getElementById("feladatDiv").style.flexDirection = "column"
        document.getElementById("tananyagDiv").style.display = "none"
    }
    if(type == "tananyag"){
        document.getElementById("tananyagDiv").style.display = "flex"
        document.getElementById("tananyagDiv").style.flexDirection = "column"
        document.getElementById("feladatDiv").style.display = "none"
    }
    else{
        document.getElementById("tananyagDiv").style.display = "none"
    }
})

let pageLink = document.getElementById("backtoPage").addEventListener("click", ()=>{
    history.back()
})

async function contentPublish(data) {
    try {
        let urlParams = new URL(location.href).searchParams;
        if (!urlParams.has('id')) {
            alert('Hiba!');
            return;
        }

        let courseId = parseInt(urlParams.get('id'));

        let reqData = {
            manage: 'content',
            action: 'create',
            course_id: courseId,
            ...data
        };

        let response = await fetch('../php/data_manager.php', {
            method: 'POST',
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify(reqData)
        });

        // TODO
        let result = await response.json();

        console.log(result);
        if (response.ok) {
            alert("Sikeres művelet!");
            history.back();
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
    let title = document.getElementById('fcim').value;
    let desc = document.getElementById('fleiras').value;
    // Az input mező értéke helyi (UTC+1) időben van. Az időzóna nincs tárolva.
    let localTime = document.getElementById('fhatarido').value;
    // Konvertálás UTC 0 időzónás dátumra (Z betű a végén). (ISO 8601 formátum)
    let due = new Date(localTime).toJSON();
    let points = parseInt(document.getElementById('fpont').value);
    let file = document.getElementById('ffile').files[0];

    if (title == '') {
        alert("A cím megadása kötelező!");
        return;
    }

    let data = {
        title,
        desc,
        task: 1,
        deadline: due,
        max_point: points,
        file
    };

    await contentPublish(data);
}

// Tananyag
async function onNewMaterial(e) {
    e.preventDefault();
    let title = document.getElementById('tcim').value;
    let desc = document.getElementById('tleiras').value;
    let file = document.getElementById('tfile').files[0];

    if (title == '') {
        alert("A cím megadása kötelező!");
        return;
    }

    let data = {
        title,
        desc,
        task: 0,
        file
    };

    await contentPublish(data);
}

document.getElementById('ujFeladatForm').addEventListener('submit', async (e) => onNewTask(e));
document.getElementById('ujTananyagForm').addEventListener('submit', async (e) => onNewMaterial(e));