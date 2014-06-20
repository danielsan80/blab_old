define([], function(){
    if (window.location.pathname.substring(0, 12) == '/app_dev.php') {
        return 'app_dev.php';
    }
    if (window.location.pathname.substring(0, 13) == '/app_test.php') {
        return 'app_test.php';
    }
    return '';
});
