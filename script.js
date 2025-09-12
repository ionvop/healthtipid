let data = loadData();

for (let element of document.querySelectorAll("*")) {
    element.style.setProperty("--none", "none");
    element.style.removeProperty("--none");
}

function loadData() {
    let data = JSON.parse(localStorage.getItem("20250904_data"));
    if (data == null) data = {};
    if (data.session == null) data.session = "";
    return data;
}

function saveData() {
    localStorage.setItem("20250904_data", JSON.stringify(data));
}

function elementFromHTML(html) {
    let template = document.createElement("template");
    template.innerHTML = html;
    return template.content.firstElementChild;
}

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}