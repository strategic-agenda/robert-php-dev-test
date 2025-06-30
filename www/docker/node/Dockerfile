FROM node:lts

RUN npm i -g npm-check-updates depcheck

WORKDIR /code

CMD /bin/sh -c 'npm install && npm start'
# ENTRYPOINT ["tail", "-f", "/dev/null"]
