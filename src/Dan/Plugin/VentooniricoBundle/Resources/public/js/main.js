require.config({
    paths: {
        'jquery': 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min',
        'json2': 'http://ajax.cdnjs.com/ajax/libs/json2/20110223/json2',
        'jquery-ui': '../jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min',
        'underscore': 'libs/underscore/underscore',
        'backbone': 'libs/backbone/backbone',
        'backbone-relational': 'libs/backbone/backbone-relational',
        
        'backbone-loader': 'libs/loader/backbone',
        'jquery-loader': 'libs/loader/jquery',
        
        'eventie': 'libs/bower_components/eventie',
        'doc-ready': 'libs/bower_components/doc-ready',
        'eventEmitter': 'libs/bower_components/eventEmitter',
        'get-style-property': 'libs/bower_components/get-style-property',
        'get-size': 'libs/bower_components/get-size',
        'matches-selector': 'libs/bower_components/matches-selector',
        'outlayer': 'libs/bower_components/outlayer',
        'masonry': 'libs/bower_components/masonry',
    },
    shim: {
        'backbone': {
            deps: ['underscore', 'jquery'],
            exports: 'Backbone'
        },
        'underscore': {
            exports: '_'
        },
        'backbone-relational': {
            deps: ['backbone'],
            exports: 'Backbone'
        }
    }

});

require([
    'app/app',
], function(App) {
    
    App.initialize();
});
