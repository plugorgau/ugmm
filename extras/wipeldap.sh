#!/bin/bash
ldapsearch -x -b "dc=plug,dc=org,dc=au" "(objectClass=x-plug-payment)" dn | grep "dn:" | cut -f 2 -d :  > /tmp/deletedn
ldapsearch -x -b "dc=plug,dc=org,dc=au" "(objectClass=groupOfNames)" dn | grep "dn:" | cut -f 2 -d :  >> /tmp/deletedn
ldapsearch -x -b "dc=plug,dc=org,dc=au" "(objectClass=posixAccount)" dn | grep "dn:" | cut -f 2 -d :  >> /tmp/deletedn
ldapdelete -W -D "cn=admin,dc=plug,dc=org,dc=au" -f /tmp/deletedn -c

