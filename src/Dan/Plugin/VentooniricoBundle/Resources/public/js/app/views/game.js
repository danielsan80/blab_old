define([
  'jquery-loader', 
  'underscore', 
  'backbone-loader',
  'app/views/game-status',
  'app/util/current-user',
], function($, _, Backbone, GameStatusView, currentUser){

    var GameView = Backbone.View.extend({
        tagName: 'tr',
        initialize: function() {
            this.listenTo(this.model, 'change', this.render);
        },
        template: _.template($('#game').html()),
        render: function() {
            this.setElement(this.template({game: this.model.toJSON()}));
            new GameStatusView({
                el: this.$el.find(".game-status"),
                model: {
                    game: this.model,
                    user: currentUser
                }
            });
            return this;
        }
    });
    return GameView;
});
