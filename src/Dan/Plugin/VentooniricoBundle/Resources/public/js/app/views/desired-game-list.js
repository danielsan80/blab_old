define([
  'jquery-loader', 
  'underscore', 
  'backbone-loader',
  'app/views/desired-game',
  'masonry/masonry',
], function($, _, Backbone, DesiredGameView, Masonry){

    var DesiredGameListView = Backbone.View.extend({
        initialize: function() {
            this.listenTo(this.model, 'sync', this.render);
        },
        template: _.template($('#desired-game-list').html()),
        render: function() {
            this.$el.html(this.template(this.model));
            this.model.forEach(this.renderGame);
            return this;
        },
        renderGame: function(game, index, games) {
            var desiredGameView = new DesiredGameView({
                model: game
            });
            var el = desiredGameView.render().el;
            this.$('div.desired-game-list').append(el);
            
            if (index==games.length-1) {
                var masonry = new Masonry(this.$('.masonry').get(0),{
                    itemSelector: '.item',
                    "gutter": 10
                });
                setInterval(function(){
                    masonry.layout();
                }, 20000)
            }
        },
        
    });
    return DesiredGameListView;
});
