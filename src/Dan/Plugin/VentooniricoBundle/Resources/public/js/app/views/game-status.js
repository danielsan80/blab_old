define([
  'jquery-loader', 
  'underscore', 
  'backbone-loader',
  'app/views/desire',
], function($, _, Backbone, DesireView){

    var GameStatusView = Backbone.View.extend({
        
        initialize: function() {
            this.listenTo(this.model.game, 'change', this.render);
            this.listenTo(this.model.user, 'change', this.render);
            this.render()
        },
        events: {
            "click .desire-create": "createDesire",
        },
        render: function() {
            var desire = this.model.game.get('desire');
            if (!desire) {
                if (this.model.user.isLogged()) {
                    if (this.model.user.canCreateDesire()) {
                        this.template = _.template($('#game-status-user-nodesire').html()),
                        this.$el.html(this.template(this.model));
                        return this;
                    } else {
                        this.template = _.template($('#game-status-user-nodesire-limited').html()),
                        this.$el.html(this.template(this.model));
                        return this;
                    }
                } else {
                    this.template = _.template($('#game-status-nouser-nodesire').html()),
                    this.$el.html(this.template({}));
                    return this;
                }
            }
            
            var desireView = new DesireView({
                model: {
                    user: this.model.user,
                    game: this.model.game,
                    desire: desire
                }                    
            });
            this.$el.html(desireView.el);
        },
        createDesire: function() {
            this.model.game.createDesire(this.model.user);
            return false;
        }
    });
    return GameStatusView;
});
