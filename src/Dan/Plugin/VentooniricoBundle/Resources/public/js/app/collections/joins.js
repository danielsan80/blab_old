define([
    'backbone-loader',
    'app/models/join',
], function(Backbone, Join){
    var JoinCollection = Backbone.Collection.extend({
        model: Join
    });
    
    return JoinCollection;
});
