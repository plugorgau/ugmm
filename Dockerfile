
FROM debian:jessie as builder

WORKDIR /usr/src/ugmm
RUN apt-get update
RUN apt-get install -y devscripts

COPY . ./
RUN debuild -i -us -uc -b
# ../plug-ugmm_*

FROM debian:jessie as ugmm
COPY --from=builder /usr/src/plug-ugmm* /tmp/
RUN apt-get update
RUN apt-get install -y gdebi
RUN gdebi -n /tmp/plug-ugmm_*_all.deb

ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2

EXPOSE 80
CMD ["apachectl", "-D", "FOREGROUND"]
