# ETML : Email Template Markup Library

This library is designed to simplify the creation of html emails formatted according to a predefined template.

It can be used to format an entire email to be used for example as a newsletter, or to define a default template where to specify different variables for each email such as the subject and the text of the message.

The operation is based on the definition of some custom html / xml tags that are then replaced with an associated html string. The logic wants to be similar to the components of libraries like reactjs or vuejs.

The actual development was designed for PHP, but I expect the javascript implementation for node.js.

***ATTENTION**: Currently the library is in a state of initial/experimental development and I recommend the use in production with caution, the some more complex cases could give unexpected results.*


## Requirements

- PHP 7.0 +
- PHP mbstring


## ToDo

- [x] Template parser
- [x] Email builder
- [x] Better manage standard global variables (subject, body, ecc)
- [x] Methods for define default properties by e-tag and by id
- [x] Setup autoload
- [ ] Live Example
- [ ] Creation of some examples
- [ ] Creation of some basic templates
- [ ] Setup for Composer



## Extra ToDo

- [ ] Class extension of [PHPMailer](https://github.com/PHPMailer/PHPMailer) for automatic layout management
- [ ] Porting to javascript version for node.js


## Notes

- For the template files I used the file extension .htm to allow the text editors to activate the html syntax. However, the content is not a valid HTML or XML structure

## License

[License](./LICENSE.md)