# Library Management System (PHP OOP + HTML/CSS)

## Files
- index.php                  - web page (forms, tables, styling)
- demo.php                   - console/text demo of all the features
- assets/css/style.css       - CSS for the web page
- classes/Book.php           - Book class
- classes/Member.php         - Member class
- classes/PremiumMember.php  - PremiumMember (inherits Member)
- classes/Library.php        - Library class (manages books and members)

## How to run
Web page:
1. Copy the folder into htdocs (XAMPP).
2. Start Apache.
3. Open http://localhost/library-management/

Or from a terminal inside the folder:
    php -S localhost:8000
Then open http://localhost:8000

Text demo:
    php demo.php

Note: data is kept in the PHP session (no database), so it resets when the session ends.
Use the "Reset data" button to go back to the sample data.
