#qCMS
A simple flat file CMS that is quick and painless to set up

##Installation

First extract the files to the same directory of the html page you wish to manage. 

Next edit admin.php and change the users array and change the files variable to the files you wish to manage

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
                <div id="body" data-qcms-ckedit="Page Content">
                        <p>This is some text woo!</p>
                </div>
		<a href="http://google.com/" data-qcms-link="Footer Link">Google!</a>
	</body>
</html>
```

###Available Input Types:

Text Input

*data-qcms-input
*```html
	<h1 data-qcms-input="Page Title">Hello World</h1>
```
Textarea

*data-qcms-textarea
*```html
	<p data-qcms-input="Some Text">This will be put inside a textarea</p>
```

Link

*data-qcms-link
*```html
	<a href="http://google.com" data-qcms-link="Google link">Google</a>
```

CkEditor

*data-qcms-ckedit
*```html
	<div data-qcms-ckedit="Main Content">
		<p>All three of these items</p>
		<p>Will be editable in the ckeditor</p>
		<img src="/someimage.png">
	</div>
```
