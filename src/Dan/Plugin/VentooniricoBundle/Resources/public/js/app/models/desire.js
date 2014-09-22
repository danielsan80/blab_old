define([
    'module',
    'backbone-loader',
    'app/models/game',
    'app/models/user',
    'app/models/join',
    'app/collections/joins',
], function(module, Backbone, Game, User, Join, JoinCollection) {

    var Desire = Backbone.RelationalModel.extend({
        urlRoot: module.config().urlRoot,
        relations: [
            {
                type: Backbone.HasOne,
                key: 'game',
                relatedModel: Game
            },
            {
                type: Backbone.HasOne,
                key: 'owner',
                relatedModel: User
            },
            {
                type: Backbone.HasMany,
                key: 'joins',
                relatedModel: Join,
                collectionType: JoinCollection
            }
        ],
        addJoin: function(user) {
            var desire = this
            var join = new Join({user: user, desire: desire});
            join.save({}, {
                success:function(e){
                    desire.get('joins').add(join);
            }});
            
        },
        removeJoinById: function(id) {
            var joins = this.get('joins');
            var join = joins.get(id);
            joins.remove(join);
            join.destroy();
            
            if (!this.get('owner') && !joins.length) {
                this.get('game').removeDesire(this);
            }
            
        },
        removeJoin: function(user) {
            var joins = this.get('joins');
            var i = 0;
            while (i < joins.length) {
                var join = joins.at(i);
                if (join.get('user').id == user.id) {
                    join.destroy();
                    joins.remove(join);
                    //this.set('joins', joins);
                    break;
                }
                i++;
            }
            if (!this.get('owner') && !joins.length) {
                this.get('game').removeDesire(this);
            }
        },
        takeDesire: function(user) {
            this.set('owner', user);
            this.save();
            user.notifyCreateDesire();
        },
        leaveDesire: function(user) {
                var owner = this.get('owner');
                this.set('owner', null);
                owner.notifyRemoveDesire();
//            if (this.get('owner').id == user.id) {
//                this.set('owner', null);
//                user.notifyRemoveDesire();
//            } else {
//                this.removeJoin(user);
//            }
            if (!this.get('joins').length) {
                this.get('game').removeDesire();
            } else {
                this.save();
            }
        }
    });

    return Desire;
});
