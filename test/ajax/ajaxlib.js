function standardize(node) {
    if (!node.addEventListener)
        node.addEventListener = function(t, l, c) { this["on"+t] = l; };
    if (!node.dispatchEvent)
        node.dispatchEvent = function(e) { this["on"+e.type](e); };
}
