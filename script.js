let g_data = g_loadData();

for (let element of document.querySelectorAll("*")) {
    element.style.setProperty("--none", "none");
    element.style.removeProperty("--none");
}

function g_loadData() {
    let data = JSON.parse(localStorage.getItem("20250904_data"));
    if (data == null) data = {};
    if (data.session == null) data.session = "";
    return data;
}

function g_saveData() {
    localStorage.setItem("20250904_data", JSON.stringify(data));
}

function g_elementFromHTML(html) {
    let template = document.createElement("template");
    template.innerHTML = html;
    return template.content.firstElementChild;
}

function g_escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

async function g_getUser() {
    let response = await fetch("api/user/", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            session: g_data.session
        })
    });

    let json = await response.json();
    console.log(json);

    if (response.ok == false) {
        location.href = "login.html";
        return;
    }

    return json.user;
}