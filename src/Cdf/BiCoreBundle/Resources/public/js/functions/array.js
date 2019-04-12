function findKeyArrayByValue(obj, val) {
    for (var i in obj) {
        if (obj.hasOwnProperty(i) && obj[i] === val) {
            return i;
        }
    }
    return null;
}
