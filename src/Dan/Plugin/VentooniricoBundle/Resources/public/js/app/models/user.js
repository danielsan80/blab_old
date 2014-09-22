define([
    'module',
    'backbone-loader',
], function(module, Backbone){
    var User = Backbone.RelationalModel.extend({
        urlRoot: module.config().urlRoot,
        desiresLimit: module.config().desiresLimit,
        notifyRemoveDesire: function() {
            this.set('desires_count', this.get('desires_count')-1);
        },
        notifyCreateDesire: function() {
            this.set('desires_count', this.get('desires_count')+1);
        },
        canCreateDesire: function() {
            return this.get('desires_count') < this.desiresLimit;
        }
        
    });
    
    return User;
});
