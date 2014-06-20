require.config({
    urlArgs: 'cb=' + Math.random(),
    paths: {
        'jquery': 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min',
        'json2': 'http://ajax.cdnjs.com/ajax/libs/json2/20110223/json2',
        'jquery-ui': '../jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min',
        'underscore': 'libs/underscore/underscore',
        'backbone': 'libs/backbone/backbone',
        'backbone-relational': 'libs/backbone/backbone-relational',
        'backbone-loader': 'libs/loader/backbone',
        'jquery-loader': 'libs/loader/jquery',
        'templates': '../templates',
        'eventie': 'libs/bower_components/eventie',
        'doc-ready': 'libs/bower_components/doc-ready',
        'eventEmitter': 'libs/bower_components/eventEmitter',
        'get-style-property': 'libs/bower_components/get-style-property',
        'get-size': 'libs/bower_components/get-size',
        'matches-selector': 'libs/bower_components/matches-selector',
        'outlayer': 'libs/bower_components/outlayer',
        'masonry': 'libs/bower_components/masonry',
        'jasmine': 'libs/jasmine-1.3.1/jasmine',
        'jasmine-html': 'libs/jasmine-1.3.1/jasmine-html',

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
        },
        'jasmine': {
            exports: 'jasmine'
        },
        'jasmine-html': {
            deps: ['jasmine'],
            exports: 'jasmine'
        }
    }
});

require(
    [
        "jasmine-html",
        "spec/default"
    ],
    function( jasmine ){

        // Set up the HTML reporter - this is reponsible for
        // aggregating the results reported by Jasmine as the
        // tests and suites are executed.
        jasmine.getEnv().addReporter(
            new jasmine.HtmlReporter()
        );

        // Run all the loaded test specs.
        jasmine.getEnv().execute();

    }
);