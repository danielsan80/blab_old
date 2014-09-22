define([
  'jquery-loader', 
  'underscore', 
  'backbone-loader',
  'app/collections/games',
  'app/collections/desired-games',
  'app/views/game-count',
  'app/views/desired-game-list',
  'app/views/game-list',
  'app/util/current-user',
], function($, _, Backbone, GameCollection, DesiredGameCollection, GameCountView, DesiredGameListView, GameListView, currentUser){
         
    var IndexView = Backbone.View.extend({
        el: $("#app"),
        template: _.template($('#games').html()),
        events: {
        },
        initialize: function() {
            this.render();
        },
        render: function() {
            this.$el.html(this.template({}));
            var gameCollection = new GameCollection();
            var desiredGameCollection = new DesiredGameCollection();

            
            var gameCountView = new GameCountView({'model': gameCollection});
            var desiredGameListView = new DesiredGameListView({'model': desiredGameCollection});
            var gameListView = new GameListView({'model': gameCollection});

            this.$("#game-list").append(gameListView.el);
            this.$("#desired-game-list").append(desiredGameListView.el);
            this.$("#game-count").append(gameCountView.el);
            
            desiredGameCollection.fetch();
            currentUser.fetch();
            gameCollection.fetch();
        },
    });

    return IndexView;

});
