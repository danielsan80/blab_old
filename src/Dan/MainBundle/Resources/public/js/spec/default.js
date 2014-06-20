define(["app/models/user", "jasmine-html"], function(User) {
    describe("User", function() {
        it("should have a name", function() {
            user = new User({name:'danilo'});
            expect(user.get('name')).toEqual('danilo');
        });
    });
    
    return true;
});
