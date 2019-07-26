
This document assumes that a separate LDAP tree (stored in its own mdb
format database) is to be added for the plug.org.au domain.  This will
most often be the case when testing on your own workstation, for
example.

== Database setup ==

    # Change into your working directory for the repo
    cd ...
    # Prepare the new database directory (if changing this, modify extras/examples/plug.ldif)
    sudo install -o openldap -g openldap -d /var/lib/ldap_plug
    # Then, use the OnLine Config mechanism to add an entry in /etc/ldap/slapd.d/cn=config
    sudo ldapadd -H ldapi:// -Y EXTERNAL -f extras/examples/plug.ldif
    # Add the schema (converted from plugpen.schema)
    sudo ldapadd -H ldapi:// -Y EXTERNAL -f extras/examples/plugpen.schema.ldif
    # Now, populate the special entries in the database
    sudo ldapadd -H ldapi:// -Y EXTERNAL -f extras/examples/plug_db.ldif
    sudo ldapadd -H ldapi:// -Y EXTERNAL -f groups.ldif
    sudo ldapadd -H ldapi:// -Y EXTERNAL -f extras/examples/specialgroups.ldif
    # And change the admin password for this database, if desired
    sudo ldappasswd -H ldapi:// -Y EXTERNAL -S cn=admin,dc=plug,dc=org,dc=au
    # Make sure extras/examples/memberof.ldif references the correct database and modify if not
    sudo ls /etc/ldap/slapd.d/cn=config
    sudo less '/etc/ldap/slapd.d/cn=config/olcDatabase={2}mdb.ldif'
    # Activate the "memberof" module to support automatic member/memberOf attributes for groups
    sudo ldapadd -H ldapi:// -Y EXTERNAL -f extras/examples/memberof.ldif
    # Add a test user
    sudo ldapadd -H ldapi:// -Y EXTERNAL -f extras/examples/bob.ldif

Lastly, you must go through and manually add the active members to the
cn=currentmembers,ou=Groups,dc=plug,dc=org,dc=au group.
