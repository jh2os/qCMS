#qCMS
A simple flat file CMS that is quick and painless to set up

##Installation

First extract the files to the same directory of the html page you wish to manage. 

Next edit admin.php and change the users array and change the file variable to the file you wish to manage

Then you edit your html file you wish to manage

so this 
```html
<html>
	<head>
		<title>Hello World</title>
	</head>
	<body>
		<h1>Hello World</h1>
		<div id="body">
			<p>This is some text woo!</p>
		</div>
		<a href="http://google.com/">Google!</a>	
	</body>
</html>
```
to:
```html
<html>
        <head>
                <title data-qcms-input="Page Title">Hello World</title>
        </head>
        <body>
                <h1 data-qcms-input="Page Title">Hello World</h1>
                <div id="body" data-qcms-input="Page Content">
                        <p>This is some text woo!</p>
                </div>
		<a href="http://google.com/" data-qcms-link="Footer Link">Google!</a>
	</body>
</html>
```
