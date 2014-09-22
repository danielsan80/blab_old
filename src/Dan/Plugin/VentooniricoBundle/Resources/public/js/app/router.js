define([
  'backbone',
  'app/views/index',
  'app/util/prefix'
], function(Backbone, IndexView, prefix) {
    
    Router = Backbone.Router.extend({
        routes: function(){
            var routes = new Array();
            routes[prefix + ""] = "index";
            routes[prefix + "/"] = "index";
            return routes;
        },
        index: function() {
            var indexView = new IndexView({});
        }
    });
    
    var initialize = function() {
//        var user = new $.ventoonirico.CurrentUser();
        var router = new Router();
        Backbone.history.start({
            pushState: true
        });
    }

    return {
        initialize: initialize
    };

});
