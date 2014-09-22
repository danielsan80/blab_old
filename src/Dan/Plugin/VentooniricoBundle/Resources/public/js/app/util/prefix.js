define([], function(){
    var url = window.location.pathname.split('/');
    if (!url[0]) {
        url = url.slice(1);
    }
    return url.join('/');
});
