define([
  'jquery-loader', 
  'underscore', 
  'backbone-loader',
], function($, _, Backbone){

    var GameCountView = Backbone.View.extend({
        initialize: function() {
            this.listenTo(this.model, 'sync', this.render);
        },
        template: _.template($('#game-count').html()),
        render: function() {
            this.$el.html(this.template({count: this.model.length}));
            return this;
        },
    });
    
    return GameCountView;
});
