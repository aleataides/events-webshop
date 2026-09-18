# Eventim - Events Webshop

Build a small event webshop with your own frontend and backend.

The webshop should allow users to browse events, view event details, and select tickets for a potential purchase.

## Technical setup

### Frontend

- Use Vue.js and Typescript
We currently use Vuetify in our product. You can use it as well, but you don't have to.
- You do not need to follow the design of our current shop.
Backend
- Use either PHP or Java: We currently use PHP with Slim and Java with Spring Boot. You are free to use another framework within PHP or Java.
- The application should use its own database.

### Event data

- You may use the existing EVENTIM.Light Event API to understand the available data structures and to obtain example data for your implementation.
- Your finished application should not make runtime requests to this Event API. The required event data should be part of your own application.
- Requests to the existing Images API are still allowed.

## Materials & Links

### Example shop

- Event overview:
  - <https://www.eventim-light.com/de/a/5da03c56503ca200015df6cb>

### Example Event Overview API

- Use this as a reference for the available data structure or as a source for example data during development
  - <https://www.eventim-light.com/de/a/5da03c56503ca200015df6cb/api/event>

### Images API

- Overview of available image types:
  - <https://bo.light-stg.de/de/api/image/5da03c56503ca200015df6cb/overview/webp>

## Page sections

- Events listing/details page can have the same header:
  - Affiliate logo - [if there is sth in the shopping cart, show it]

### Events list page

- Filter - Search input
- Events list
  - Item
    - Image
    - Image copyright (Image: Photographer name)
    - Event name
    - Event location (white label’s location - most probably)
    - Date / time
    - Cheapest price / Not Available
  - Pagination: cursor pagination

- Event details page
  - Event image
    - Image copyright (Image: photographer’s name)
  - Event name
    - Event short details
  - Event address (with maps)
    - Show map collapse -> lazy load the map
  - Event date / time
  - Event information/description
    - With show more
  - Price/access info
  - Tickets “cart” section
    - Ticket name/type
    - Ticket price
    - Selected amount
      - Once amount > 1, update the “add to shopping cart” button total price + tickets count
  - Tickets information
  - Add to shopping card button

- Event shopping cart
  - Affiliate logo
  - Shopping cart expiration time (15 min)
  - Order details
    - Total amount
    - Order items
      - Image | Event title - event location - event date /time - delete from cart
    - Tickets count - Edit button (per event)
      - Edit modal
        - You can edit the ticket’s amount
        - If zero, remove from the cart
      - Ticket name
        - Freie Platzwahl - Normalpreis (1) - price
    - Total + VAT info
    - Checkout info/helper
    - Continue shopping (events list home)
    - Buy

## Requirements

- Each event has N categories
- Filter + input search on events listing
- Cursor pagination: as there isn’t a sort, a simple uuid can be used for the event IDs (verify later)
  - Or if it’s available, use the uuid that allows sorting (timestamps)
- Vue router, no admin area.
- Shopping cart: check if pessimistic lock is needed/recommended for this task, but use a lock system
  - it should be handled by the back-end always
- Loading state between route navigation
- always create respecitve tests for FE and BE when necessary
- use max 2 lines docblock comment
- use PHP's PSR-12 + Slim's best practices.
- use Vuejs + Vuetify recommended best practices
- create a .editorconfig file with all the recommended styling above
- before commiting something, run the tests/lints/phpstan and/or other code quality tool
- prefer DI over static
- do not use business rules in the controller
  - controller > service > repository
- when possible, use laravel-like resource class for the responses
- always return json
- add api throttling limit
- maybe an affiliate context (and) middleware would be good, so that the repositories/services don't need to be aware of which affiliate is requesting.
- redis cache could be used for the events, since it wont be changed often
  - handle the cache miss
- create a docker image where I can run both FE/BE in their own container
- create a seeder factory (faker)
- use slim and vuetify + respective tests/code quality tools
- create a gh action to check the security/quality gate/tests/whatever needed to keep the code quality when creating a PR.
- the layout will be defined later
