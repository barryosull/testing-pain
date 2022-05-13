# Hidden Concepts

This refactor has made things a lot clearer, let's look at the pain points its highlighted and figure out how we could change our tests and our code to address it.

## Dependency injection
First off, we're injecting our dependencies via magic. This makes the code practically impossible to reason about, we don't know when we're dealing with a real instance or a mocked one. The solution here is simple, rather than using magic we should inject all our dependencies in the controller's constructor, make it super expressive.

Side note, we will quickly find that there are lots of dependencies, that's fine, this pain was always there, it was just hidden. Now we're making that pain clear and can address it later.

## Repositories
Second off, we are directly touching the database. The tables schemas are bleeding directly into the controller test and they're just muddying the water.

The solution here is to introduce repositories. We would create a repository for each of these concepts (entities). We'd then use those repos to seed data in our "given" methods and fetch the entities in our "verify" methods. We'd inject those same repos inton our controller and use them to fetch our entities and store them after they are changed.

With that done the DB concept is completely removed from our controller test. We could even go a step further and create an in memory implementation of those repos and use those instead. Now our controller test doesn't require a database at all and becomes a "true" unit test. Ports and adapters in action!


## HTTP adapter
Ok, what else can we see? Well, we have a lot of factory code that creates similar structures with repeating data and minimal changes, these are then used to test failure states. These tests aren't testing the behaviour of our domain like the rest of the tests, they're validating the input. It would be better if we separated the code that tests input details (HTTP validation) from the code that tests behaviour.

We could easily extract these tests their own file, which screams that there's a concept that could be extracted from the controller code as well, probably an adapter that takes in the HTTP input and validates it. That would make both the controller and its test code simpler.

## Value Objects
There are a lot of primatives used in the factory and verify methods. It sure would be simpler if we bundled that data together into ValueObjects and used those instead. It would make the verification of data changes a lot easier as well. We could even tie that concept into the adapter concept above, make the adapter output ValueObjects instead of primatives.

That's just a taste of what we can do when we listen to test pain. 

We've talked about what we _could_ do, next let's look at actually doing it with another example.

