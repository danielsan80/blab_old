define([
  'jquery-loader', 
  'underscore', 
  'backbone-loader',
  'app/collections/users',
], function($, _, Backbone, UserCollection){

    var DesireView = Backbone.View.extend({
        initialize: function() {
            this.listenTo(this.model.desire, 'change', this.render);
            this.listenTo(this.model.desire, 'add:joins', this.render);
            this.listenTo(this.model.desire, 'remove:joins', this.render);
            this.setElement($('#game-status-desire').html());
            this.render();
        },
        events: {
            "click .join-add": "addJoin",
            "click .join-remove": "removeJoin",
            "click .desire-take": "takeDesire",
            "click .desire-leave": "leaveDesire"
        },
        render: function() {
            var user = this.model.user;
            var game = this.model.game;
            var desire = this.model.game.get('desire');
            
            if (desire.get('owner')) {
                this.$el.html(_.template($('#game-status-desire-owner-player_main').html(), {user: user, owner: desire.get('owner')}));
            } else {
                if (user.canCreateDesire()) {
                    this.$el.html(_.template($('#game-status-desire-noowner-player_main').html(), {user: user, owner: desire.get('owner')}));
                } else {
                    this.$el.html(_.template($('#game-status-desire-noowner-player_main-limited').html(), {user: user, owner: desire.get('owner')}));
                }
            }

            var joins = desire.get('joins');
            var users = new UserCollection([desire.get('owner')]);
            
            for(var i=0; i<game.get('maxPlayers')-1; i++) {
                var join  = joins.at(i);
                if ( join) {
                    var guest = users.get(join.get('user')) != undefined;
                    users.push(join.get('user'));
                    this.$el.append(_.template($('#game-status-desire-player_joined').html(), {user: user, join: join, guest:guest}));
                } else {
                    this.$el.append(_.template($('#game-status-desire-player_nobody').html(), {user: user}));
                }
            }
    
            return this;
        },
        addJoin: function() {
            this.model.desire.addJoin(this.model.user);
            return false;
        },
        removeJoin: function(e) {
            var id = $(e.currentTarget).data('join-id');
            this.model.desire.removeJoinById(id);
            //this.model.desire.removeJoin(this.model.user);
            return false;
        },        
        takeDesire: function() {
            this.model.game.takeDesire(this.model.user);
            return false;
        },
        leaveDesire: function() {
            this.model.game.leaveDesire(this.model.user);
            return false;
        }
    });
    return DesireView;
});
