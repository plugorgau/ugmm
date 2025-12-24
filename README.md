# Perth Linux Users Group - User Group Members Management

UGMM is a simple tool written to manager PLUG's members stored in an
LDAP database.

An LDAP database may not be the best tool for storing this data,
however it does provide central authentication which was key at the
time UGMM was written.

## Building

Build a debian package to install, rather than trying to run from
source. This allows easy updating of production hosts, with a rollback
by installing the previous version.

To build, run
```
debuild -i -us -uc -b
```

This will give you a file like plug-ugmm_0.8.0_all.deb which you can then install with the command
```
sudo apt-get install plug-ugmm_0.8.0_all.deb
```

## LDAP Configuration

UGMM makes a few assumptions about the schema of the LDAP server:

1. The [RFC 2307bis][rfc2307bis] schema must be loaded. Note that this
   schema conflicts with the `nis` schema (aka RFC 2307) distributed
   by OpenLDAP. In particular, UGMM depends on the following features:
    * `posixAccount` is an auxiliary object class that can be combined
      with structural object classes like `groupOfNames`.
    * `shadowExpire` defines an ordering.
2. The [`namedobject` schema][namedobject] must be loaded. This is
   used for the `maxUid` entry to keep track of the last assigned
   IDs. The version distributed with OpenLDAP is fine.
3. The OpenLDAP `memberof` overlay must be loaded, to automatically
   keep `member` / `memberOf` attributes in sync.
4. The `plugpen` schema must be loaded. This is a custom schema used
   to keep track of payments made by members. A copy of this schema is
   installed to `/etc/ldap/schemas`.

It also expects a certan hierarchy below the base DN:

1. an `ou=Users` organizational unit as the parent for users.
2. an `ou=Groups` organizational unit as the parent for groups.
3. an `ou=UPG,ou=Groups` organizational unit for [user private groups][UPG].
4. a `cn=maxUid,ou=Users` entry to manage the highest issued user ID.
5. a number of groups under `ou=Groups`:
    * `currentmembers`, `pendingmembers`, `overduemembers`, and
      `expiredmembers` groups to manage membership status.
    * A `shell` group to track which members have requested shell accounts.
    * A `committee` group to control access to the administrative
      portions of the app.

There are ldif files in `examples/ldap` that can be used as reference.

[rfc2307bis]: https://datatracker.ietf.org/doc/html/draft-howard-rfc2307bis-02
[namedobject]: https://datatracker.ietf.org/doc/html/draft-stroeder-namedobject
[UPG]: https://wiki.debian.org/UserPrivateGroups

## System package configuration

Copy `/usr/share/plug-ugmm/lib/PLUG/ldapconnection.inc.php.example` to
`/etc/private/ldapconnection.inc.php` and modify it to suit your LDAP
database.

This config file contains credentials for connecting to the LDAP
database, so read access should be limited to the user php is running
as (probably `www-data`).

## Web server configuration

These two sections assume that your desired web server software is
already installed.  They also assume that your DNS and firewall settings
are configured appropriately.

### Setting up UGMM to run under Apache

First, create/edit an Apache virtual host config file (with .conf
extension) in `/etc/apache2/sites-available/`.  Fill out the virtual
host details and HTTPS settings if not done already.  Then, copy-paste
a snippet from
`/usr/share/doc/plug-ugmm/examples/apache/plug-ugmm.conf` (or
`/usr/share/doc/plug-ugmm/examples/apache/plug-ugmm.section.conf` if
running under the /ugmm URL path instead of on a dedicated Apache
virtual host) into the virtual host file to activate UGMM at a given
path under your site's URL.

If creating a new virtual host file, run `sudo a2ensite <sitename>`
where "\<sitename\>" is the basename of your file without the extension.

Lastly, run these commands:

```
sudo a2enmod rewrite
sudo apachectl -t
# Do not proceed if the previous command reported errors
sudo service apache2 restart
```

### Setting up UGMM to run under Nginx

First, create/edit an Nginx virtual host config file (with .conf
extension) in `/etc/nginx/sites-available/`.  Fill out the virtual
host details and HTTPS settings if not done already.  Then, copy-paste
a snippet from
`/usr/share/doc/plug-ugmm/examples/nginx/plug-ugmm.conf` (or
`/usr/share/doc/plug-ugmm/examples/nginx/plug-ugmm.section.conf` if
running under the /ugmm URL path instead of on a dedicated Nginx
virtual host) into the virtual host file to activate UGMM at a given
path under your site's URL.

If creating a new virtual host file, run

```
sudo ln -s ../sites-available/<conffile> /etc/nginx/sites-enabled/
```
where "\<conffile\>" is the basename of your file.

If necessary, modify the `fastcgi_pass` statement to use the correct FPM
socket path.

Lastly, run these commands:

```
sudo nginx -t
# Do not proceed if the previous command reported errors
sudo service nginx reload
```
