2.0.4-p27 / 2016-11-09
===================

 * Added: send a test email of campaign
 * Added: better internationalization support: allow creating new language
 * Added: better internationalization support: support custom translation
 * Changed: support running several campaigns at the same time

2.0.4-p26 / 2016-11-08

 * Added: send a test email of campaign
 * Added: better internationalization support: allow creating new language
 * Added: better internationalization support: support custom translation
 * Changed: support running several campaigns at the same time

2.0.4-p25 / 2016-11-01
==================

 * Fixed: certain encoding may cause corrupt links
 * Changed: default user policy change

2.0.4-p24 / 2016-10-28
==================
 
 * Fixed: subscriber import does not work well with async
 * Fixed: runtime-message-id with extra invisible space
 * Fixed: directory permission checking error
 * Fixed: campaign's wrong subscribers count in certain cases
 * Fixed: config cache with invalid values

2.0.4-p23 / 2016-10-23
==================

 * Added: ElasticEmail API/SMTP support
 * Fixed: reduce the delay time when sending email through SMTP
 * Changed: delivery server encryption method is no longer required

2.0.4-p22 / 2016-10-19
==================
 
 * Added: create-user API
 * Added: quick login support
 * Added: copy campaign
 * Fixed: detect more environment dependencies when installing
 * Fixed: layout crashes for old IE browser
 * Fixed: application crashes when mbstring is missing
 * Fixed: chart view issues on MS Edge

2.0.4-p20 / 2016-10-12
==================

 * Fixed: installation wizard compatibility issue
 * Added: drag & drop email builder

2.0.4-p19 / 2016-10-03
==================

 * Fixed: certain types of links are not tracked

2.0.4-p18 / 2016-10-02
==================

 * Fixed: open tracking causes broken image in email content

2.0.4-p17 / 2016-10-02
==================

 * Fixed intermittent issues with bar chart in Safari
 * Changed click-to-open ratio is now based on open count

2.0.4-p16 / 2016-09-30
==================

 * Fixed listing sometimes crashes due to slow internet connection
 * Fixed do not allow users to enter invalid IMAP encryption method
 * Fixed list import intermittent issue for ISO encoded CSV
 * Added pie chart visualization for top countries by open
 * Added pie chart visualization for top countries by click
 * Updated text & hints on the UI
 * Changed dashboard UI now contains more information
 * Changed click-rate is no longer computed based on specific URL

2.0.4-p11 / 2016-09-27
==================

 * Fixed SSL issue for bounce handler
 * Fixed bounce handler does not work correctly for certain type of IMAP servers
 * Changed sending campaign can be deleted
 * Added full support for SendGrid (web API & SMTP)

2.0.4-p8 / 2016-09-20
==================

 * Fixed HTML editor sometimes crashes on MS Edge 
 * Added clean up invalid bytes sequence in email content
 * Added check php-gd library availability in the installation wizard

2.0.4 / 2016-09-13
==================

This is the first publicly released version of Acelle Mail webapp (which was previously Turbo Mail 1.x, a private project at National Information System institute)

 * Fixed better compatibility with MS Edge browser
 * Multi-process support for sending large amounts of email
 * Added Mailgun API/SMTP integration full support
 * Added embeded form customization support
 * Added email extra headers for better RFC compliance
 * Added template gallery & template customization support

2.0.3 / 2016-07-01
==================

 * Added DKIM singing support for out-going message
 * Added better integration with Amazon SES
 * Added template preview support
 * Added bounce logging with more information
 * Changed refractor of quota system
