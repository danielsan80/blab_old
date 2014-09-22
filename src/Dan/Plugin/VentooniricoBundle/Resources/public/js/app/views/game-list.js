define([
  'jquery-loader', 
  'underscore', 
  'backbone-loader',
  'app/views/game',
], function($, _, Backbone, GameView){

    var GameListView = Backbone.View.extend({
        initialize: function() {
            this.listenTo(this.model, 'sync', this.render);
        },
        template: _.template($('#game-list').html()),
        render: function() {
            this.$el.parents().find(".loading").hide();
            this.$el.html(this.template(this.model));
            this.model.forEach(this.renderGame);
            return this;
        },
        renderGame: function(game) {
            var gameView = new GameView({
                model: game
            });
            this.$('table.game-list').append(gameView.render().el);
        }
    });
    return GameListView;
});
