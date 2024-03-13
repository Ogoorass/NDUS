FROM debian:bullseye-20240211

WORKDIR /var/www/html

EXPOSE 80

RUN apt update && \
    apt install -y apache2 php

RUN service apache2 start

COPY *.* . 
COPY json/ json/
COPY Pictures/ Pictures/
COPY fontello/ fontello/

RUN rm /var/www/html/index.html

CMD [ "./app.sh" ]