if (undefined == dan) {
    var dan = {};
}

dan.diary = {}

dan.diary.parseReportUrl = '';

dan.diary.lastKeyPressTime = 0;

dan.diary.delay = 500;

dan.diary.onKeyUp = function(e, func, delayed) {

    var now = Date.now();
    if (!delayed) {
        lastKeyPressTime = now;
    }

    if (now - lastKeyPressTime >= dan.diary.delay) {
        func(e);
    } else {
        if (!delayed) {
            setTimeout(function(){ dan.diary.onKeyUp(e,func, true);}, dan.diary.delay);
        }
    }
}

dan.diary.onTextChange = function(e) {
    var $content = $(e.target);
    var data = {};
    data.content = $content.val();
    var url = dan.diary.parseReportUrl;
    $.ajax({
        data: JSON.stringify(data),
        url: url,
        dataType: 'json',
        contentType: 'application/json; charset=UTF-8',
        type: 'POST',
        success: function(data) {
            console.log(data);
        },
        error: function() {
            console.log('error');
        },
    })
}

dan.diary.initReportForm = function() {

    $('#dan_plugin_diary_report_content').on('keyup', function(e) {
        dan.diary.onKeyUp(e, dan.diary.onTextChange);
    })


}


dan.diary.init = function (args) {
    $.extend(dan.diary, args);

    dan.diary.initReportForm();
}

dan.diary.init();