if (!document.querySelectorAll) {
    document.querySelectorAll = function (selector) {
        var doc = document,
            head = doc.documentElement.firstChild,
            styleTag = doc.createElement('STYLE');
        head.appendChild(styleTag);
        doc.__qsaels = [];

        if (styleTag.styleSheet) {   // for IE
            styleTag.styleSheet.cssText = selector + "{x:expression(document.__qsaels.push(this))}";
        } else {                // others
            var textnode = document.createTextNode(selector + "{x:expression(document.__qsaels.push(this))}");
            styleTag.appendChild(textnode);
        }
        window.scrollBy(0, 0);

        return doc.__qsaels;
    }
}

if (!document.querySelector) {
    document.querySelector = function (selectors) {
        var elements = document.querySelectorAll(selectors);
        return (elements.length) ? elements[0] : null;
    };
}

if (typeof HTMLElement != "undefined") {
    HTMLElement.prototype.querySelector = document.querySelector;
    HTMLElement.prototype.querySelectorAll = document.querySelectorAll;
}
else {
    var a = document.getElementsByTagName("*"), l = a.length, i;
    for (i = 0; i < l; i++) {
        a[i].querySelector = document.querySelector;
        a[i].querySelectorAll = document.querySelectorAll;
    }
}