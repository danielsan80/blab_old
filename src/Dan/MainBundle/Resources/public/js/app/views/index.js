define([
  'jquery-loader', 
  'underscore', 
  'backbone-loader',
], function($, _, Backbone){
         
    var IndexView = Backbone.View.extend({
        el: $("#app"),
        events: {
        },
        initialize: function() {
            this.render();
        },
        render: function() {
        },
    });

    return IndexView;

});
