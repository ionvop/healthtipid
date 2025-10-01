let g_data = g_loadData();
g_initialize();

function g_initialize() {
    document.body.onload = () => {
        for (let element of document.body.querySelectorAll("*")) {
            element.style.setProperty("--test", "test");
            element.style.removeProperty("--test");
        }
    };
}

function g_loadData() {
    let data = JSON.parse(localStorage.getItem("20250904_data"));
    if (data == null) data = {};
    if (data.session == null) data.session = "";
    return data;
}

function g_saveData() {
    localStorage.setItem("20250904_data", JSON.stringify(g_data));
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
    let response = await fetch("../api/user/", {
        method: "GET",
        headers: {
            "Content-Type": "application/json",
            "Authorization": g_data.session
        }
    });

    let data = await response.json();
    console.log(data);

    if (response.ok == false) {
        location.href = "login.html";
        return;
    }

    return data.user;
}