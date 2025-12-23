#!/bin/sh

set -ex

curdir="$(dirname "$0")"
examplesdir="$curdir/../examples/ldap"

echo $curdir

export DEBIAN_FRONTEND=noninteractive

# First install slapd without setting up the database
debconf-set-selections <<\EOF
slapd slapd/no_configuration boolean true
EOF
apt-get install -y slapd ldap-utils

# Replace nis schema with rfc2307bis
cp $examplesdir/rfc2307bis.ldif /etc/ldap/schema/
sed -i 's/nis\.ldif/rfc2307bis.ldif/' /usr/share/slapd/slapd.init.ldif

# Now use set up the database with the appropriate domain and password
debconf-set-selections <<\EOF
slapd slapd/no_configuration boolean false
slapd slapd/domain string plug.org.au
slapd shared/organization string plug.org.au
slapd slapd/password1 password plug
slapd slapd/password2 password plug
slapd slapd/purge_database boolean false
slapd slapd/move_old_database boolean true
EOF
dpkg-reconfigure slapd

# Load and enable the memberof plugin
ldapadd -H ldapi:// -Y EXTERNAL -f $examplesdir/load-memberof.ldif

# Add UGMM's plugpen schema
ldapadd -H ldapi:// -Y EXTERNAL -f /etc/ldap/schema/namedobject.ldif
ldapadd -H ldapi:// -Y EXTERNAL -f $curdir/../plugpen.ldif

# Load basic structure
ldapadd -H ldapi:// -D cn=admin,dc=plug,dc=org,dc=au -w plug -f $examplesdir/basic-structure.ldif
ldapadd -H ldapi:// -D cn=admin,dc=plug,dc=org,dc=au -w plug -f $examplesdir/specialgroups.ldif

# Add example users
ldapadd -H ldapi:// -D cn=admin,dc=plug,dc=org,dc=au -w plug -f $examplesdir/user-chair.ldif
ldapadd -H ldapi:// -D cn=admin,dc=plug,dc=org,dc=au -w plug -f $examplesdir/user-bob.ldif
