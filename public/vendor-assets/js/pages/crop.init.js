window.onload = function() {
    "use strict";
    var d, c = window.Cropper,
        r = document.querySelector(".img-container").getElementsByTagName("img").item(0),
        m = {
            aspectRatio: 16 / 9,
            preview: ".img-preview",
            ready: function(e) {
                console.log(e.type)
            },
            cropstart: function(e) {
                console.log(e.type, e.detail.action)
            },
            cropmove: function(e) {
                console.log(e.type, e.detail.action)
            },
            cropend: function(e) {
                console.log(e.type, e.detail.action)
            },
            crop: function(e) {
                var t = e.detail;
            },
            zoom: function(e) {
                console.log(e.type, e.detail.ratio)
            }
        },
        p = new c(r, m),
        v = r.src;
    document.querySelector(".docs-toggles").addEventListener("change", function(e) {
        var t, o, a, n, e = e || window.event,
            e = e.target || e.srcElement;
        p && (a = "checkbox" === (e = "label" === e.tagName.toLowerCase() ? e.querySelector("input") : e).type, n = "radio" === e.type, a || n) && (a ? (m[e.name] = e.checked, t = p.getCropBoxData(), o = p.getCanvasData(), m.ready = function() {
            console.log("ready"), p.setCropBoxData(t).setCanvasData(o)
        }) : (m[e.name] = e.value, m.ready = function() {
            console.log("ready")
        }), p.destroy(), p = new c(r, m))
    }),  document.body.onkeydown = function(e) {
        var t = e || window.event;
        if (t.target === this && p && !(300 < this.scrollTop)) switch (t.keyCode) {
            case 37:
                t.preventDefault(), p.move(-1, 0);
                break;
            case 38:
                t.preventDefault(), p.move(0, -1);
                break;
            case 39:
                t.preventDefault(), p.move(1, 0);
                break;
            case 40:
                t.preventDefault(), p.move(0, 1)
        }
    }
};