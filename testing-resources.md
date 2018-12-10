Hey!

After posting the [Test Driven Laravel screencast](http://adamwathan.me/2016/01/11/test-driven-laravel-from-scratch/) last week, a lot of people asked if I could recommend any other good testing resources, so I put together a list of the most important stuff I've read/seen that's really shaped the way I think about testing web applications.

I'm also working on another screencast and blog post that should be out this week that covers writing your own test doubles from scratch, and how you can simplify your tests by using fakes instead of mocks, so watch for that soon!

If there's anything you think I've missed, or you have other recommendations that have been really helpful to you, let me know! Always looking for more content to learn from.

1. ["The Magic Tricks of Testing" by Sandi Metz](https://www.youtube.com/watch?v=URSWYvyc42M)

    This is probably my all-time favorite presentation on testing. Sandi has an amazing talent for taking things that seem complicated and making them simple and easy to understand. If you've ever been confused about when you should use a stub vs. a mock, when to use test doubles vs. a real object, or what the difference is between classicist and mockist testing, this talk clears it up brilliantly by removing a lot of the confusing terminology and just focusing on what you're actually trying to accomplish and why.

    The testing chapter in her book ["Practical Object Oriented Design in Ruby"](http://www.amazon.com/gp/product/0321721330) covers the same material in more detail if you'd rather read than watch, and is a brilliant book on object oriented fundamentals in general. Highly recommended!

2. ["The RSpec Book" by David Chelimsky](https://pragprog.com/book/achbd/the-rspec-book)

    At first glance you would think this book is just a guide to using RSpec, the Ruby testing library, but the title of this book totally doesn't do it justice.

    This book goes into a ton of detail about outside-in TDD and BDD workflows, and is loaded with real-world advice about doing BDD in Rails (which happens to translate quite nicely to Laravel).

3. ["TDD, where did it all go wrong?" by Ian Cooper](https://vimeo.com/68375232)

    This is a great presentation around refocusing on the goals of TDD, and how to avoid over-testing yourself into a trap where you can't refactor.

    The most interesting idea I took away from this presentation was "don't add tests while you're refactoring". Definitely a must watch!

4. ["Growing Object Software Guided by Tests" by Steve Freeman and Nat Pryce](http://www.amazon.com/Growing-Object-Oriented-Soft...)

    This is definitely the heaviest resource of the three, but totally worth it. Steve and Nat were a big part of pioneering the use of mock objects, and this book is the canonical resource on using a mockist approach to help design your applications.

    The examples are in Java which can be a little tough if you've never worked with the language before, but it's similar enough to PHP that you should be able to follow along with a little bit of effort.

    It took me a while to get through this one, but I took away a lot of important insights and am really glad I worked through it. I wouldn't recommend it as a starting point, but it's definitely worth a read once you start getting comfortable with the fundamentals.

    So there you go! If you watch either of those videos or read either of those books, I'd love to know what you think, so definitely shoot me an email. Love talking testing!

– Adam
