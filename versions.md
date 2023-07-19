#VERSION 0.10 (MAJOR UPDATE):
- Updated tables
- Install plugin impr.

#VERSION 0.11:
- uzivatel-nahlasene-skladky check if user is registered fix
- registracia-validacia add id_user into unknow_user
- uzivatel-nahlasene-skladky fix for get skladky

#VERSION 0.12:
- Exception message

#VERSION 0.13:
- Install.php insert typy

#VERSION 0.14:
- Added ucet-prehlad

#VERSION 0.15:
- ucet-prehlad fix parametrov

#VERSION 0.16:
- fix pre ucet-prehlad pocitanie
- pridanie vycistit page

#VERSION 0.17:
- update pred ostrym serverom

#VERSION 0.18:
- image upload base64

#VERSION 0.19:
- nahlasit .jpg, .jpeg extensions

#VERSION 0.20:
- androidOrIos

#VERSION 0.21:
- trieda delete

#VERSION 0.22 (MAJOR UPDATE):
- Pridane zmena-hesla, zmena-mena
- Mail v registracii
- Slovenske exception hlasky
- Mail v token expiracii
- password_1 password_2 v registracii povinne

#VERSION 0.23:
- zabudnute-heslo
- zabudnute-heslo-validacia
- zabudnute-heslo-nove-heslo

#VERSION 0.24:
- zabudnute-heslo-nove-heslo fixnutie idUser povinne
- deleteSpaces funkcia, odstranenie medzier z mailu
- Token::$types (select, insert)
- Token fix ORDER BY id desc  

#VERSION 0.25:
- nahlasit, porovnanie ci neexistuje v blizkosti ina skladka
- Response::throwExceptionWithData
- registracia-validacia vratenie zostavajucih pokusov

#VERSION 0.26:
- prihlasenie vratenie id a name
- zmena-hesla-validacia vratenie zvysnych pokusov
- disable near fix

#VERSION 0.27:
- pivinny image pri nahlasovani
- fix pretypovanie array ucet-prehlad
- skaldky-vycistene
- skladky detail types

#VERSION 0.28 (MAJOR UPDATE):
- install.php fixnutie nazvov pre typy odpadu skladky
- vycistit dorobenie funkcionality
- oprava error messages
- pridane install skladky_vycistene_gallery, skladky_vycistene
- install fedd pridanie leg skladok

#VERSION 0.29:
- zaznamenat-aktivitu automaticke prihlasovanie overovanie
- Response throwWarning
- delenie nulou error ucet-prehlad pridanaie 
- logovanie android ios iba pre vynbrane pages
- pridany page prehlad

#VERSION 0.30:
- skladka vracia pole images s link obrazkom
- potvrdil-som vracia confirmedByUser a reportedByUser
- debug securiter
- fix a pridane FILES_URL
- priadanie texts app

#VERSION 0.31:
- geocoding nahlasit
- pridany install skladky kraj
- pridana validacia prihlasit, reg parzdy mail a hesla

#VERSION 0.32
- skladky-vsetky vracanie types
- fixnutie chyby pri zaznamenat-aktivitu (vracanie udajov ak je uzivatel prihalseny)
- dlzka mena do 30 znakov
- skladka neexistuje osetrenie v skladka (detail)
- skaldka detail fix id_skaldka v url pre images

#VERSION 0.33
- text install.php pridane init texty
- nahlasit a skladky pridany inpout velkost pre IOS
- cron mesacny 
- tokens stranka
- commno getDeviceTyp fix 

#VERSION 0.34
- nahalsit velkost if 0 retype to null
- sidlo geocoidng cela adresa
- disabled app config
- disable screens in apps
- skladky-simple typ 2
- fix ratanie bodov a registracia users

#VERSION 0.35
- idUser is necessary
- registracia-validacia vracanie login data
- zmena-hesla vracanie userData
- bug fix prihlasenie ak sa prihalsim z cudzieho mobilu
- ucet-vymazat
- insert-ideas

#VERSION 0.36 (MAJOR)
- unknown_users_users_cross
- skladky, potvrdenia pridany id_user, id_uknonw_user ako nullable
- change logic with id_user

#VERSION 0.37 (FIX)
- MAIL FIX, test-mail delete
- config DISABLE_MAIL
- GEOCODING pozutie libky php

#VERSION 0.38 (FIX)
- novy mail format
- fix nefunkcne nahlasovanie
- skladka iba na uzemi slovenska
- skladka vracia reported_by
- throwException s error logom
- limitovanie reportov za den na 3

#VERSION 0.39
- afterPhoto vycistit
- ml_recognition tabulka a endpoint
- id_skladka v skaldky vycistene pridane
- id_user_cleared
- weekly cron
- prehlad, ucet_prehlad vycistene
- name conventions cleared to cleaned

#VERSION 0.40
- verzia endpoint
- refactoring kodu common
- response::get
- mailer fix
- install udpate before release
- mail notifications
- registracia ak je ucet nie je verifikovany posli znova kod
- uprava response::get pridany exit

#VERSION 0.41
- prepocet bodov prehlady
- rok_zacatia na date

#VERSION 0.42
  - FIX posielanie notifikacii

#VERSION 0.43
- Pridane images

#VERSION 0.44
- Vycistis pridane afterPhotos a beforePhotos
- skladky-vsetky-simple zjedodusene iba pre mapu

TODO: 
- FILTROVANIE vracianie zoradenie podla vzdialenosti
- FILTROVANIE pomocou typu odpadu
- FILTROVANIE pomocou krajov (NEEXISTUJE V APPKE)
- PREHLAD vracanie poslednej nahlasenej skladky (NEEXISTUJE V APPKE)
- PRIHLASENIE ak nie je ucet verifikovany tak poslat mail ak preslo viac nez 10 min
