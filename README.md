AV_SearchReport
=====================
- The extension based on search terms from catalogsearch/query table. A report is published weekly with maximum 500 keywords in the form of a CSV file under var/search_report and it will be sent with attachment by e-mail.
- Most relevant is the "Search query", "All search result" and "Number of visit" column in CSV, analogous like search terms in Magento Backend.
- The column 'popularity' is in database emptied (After send a mail). Then the new weekly statistic begins.

Installation Instructions
-------------------------
1. Install the extension via GitHub, and deploy with modman or with composer
2. Clear the cache, logout from the admin panel and then login again.

Uninstallation
--------------
1. Remove all extension files from your Magento installation OR
2. Modman remove AV_SearchReport & modman clean

Support
-------
If you have any issues with this extension, open an issue on [GitHub](https://github.com/adamvarga).

Contribution
------------
Any contribution is highly appreciated. The best way to contribute code is to open a [pull request on GitHub](https://help.github.com/articles/using-pull-requests).

Developer
---------
Adam Varga