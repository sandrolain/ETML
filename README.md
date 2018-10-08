# ETML : Email Template Markup Library

This library is designed to simplify the creation of html emails formatted according to a predefined template.

The operation is based on the definition of some custom html / xml tags that are then replaced with an associated html string. The logic wants to be similar to the components of libraries like reactjs or vuejs.

The actual development was designed for PHP, but I expect the javascript implementation for node.js.

***ATTENTION**: Currently the library is in a state of initial + experimental development and I recommend the use in production with caution, the some more complex cases could give unexpected results.*

## ToDo

- [x] Template parser
- [x] Email builder
- [ ] Better manage standard global variables (subject, body, ecc)
- [ ] Extended test cases
- [ ] Creation of some basic templates
- [ ] Creation of sime examples
- [ ] Setup autoload
- [ ] Setup for Composer

## Extra ToDo
- [ ] Porting to javascript version for node.js