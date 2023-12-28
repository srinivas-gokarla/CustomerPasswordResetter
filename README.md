<h1 align="center">Customer Login Password Reset-magento 2</h1>

<div align="center">
  <img src="https://img.shields.io/badge/magento-2.X-brightgreen.svg?logo=magento&longCache=true" alt="Supported Magento Versions" />

## Table of contents
<div align="left">


- [Usage](#usage)
- [Installation](#installation)
- [Credits](#credits)

## Usage

To enhance customer security, Magento 2 does not offer a default
feature for sending email reminders with a custom template to
customers, prompting them to change their passwords.
<h3>Solution</h3>
Here is the solution: This module has been created to enhance customer login security.
The customer has not changed his password for many days
, then we will remind the customer to change the password
through an email.

<br>


<img src="https://imgur.com/aBa8Wuu.png" />


<img src="https://imgur.com/XibsWDA.png" />



## Installation

**Using Composer**

* composer require srinivas/module-passwordresetter
* bin/magento setup:upgrade
* bin/magento setup:di:compile

**_or_**

* Click Code dropdown in githb & Download
* Unzip the file
* Create Directory(s) as app/code/Srinivas/PasswordResetter
* copy all above files and paste inside the folder PasswordResetter
* bin/magento setup:upgrade
* bin/magento setup:di:compile

## Credits

### Srinivas Gokarla

My name is Srinivas Gokarla and I'm the creator of this repo. I'm a Magento 2 Developer.
- <a href="https://www.linkedin.com/in/srinivas-gokarla-4a4a31226/" target="_blank">🔗 Connect with me on LinkedIn</a>
- <a href="mailto:gokarlasrinivas99@gmail.com">💌 Contact me</a>
