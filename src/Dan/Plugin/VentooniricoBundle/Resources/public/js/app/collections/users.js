define([
    'backbone-loader',
    'app/models/user',
], function(Backbone, User){
    var UserCollection = Backbone.Collection.extend({
        model: User
    });
    return UserCollection;
});
