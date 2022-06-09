# BUILD STAGE WITH NODE/NPM
FROM node:16-alpine as build

# COPY IN PROJECT TO CONTAINER IMAGE
COPY . .

# INSTALL BUILD DEPS
RUN npm ci

# SET NODE_ENV TO BUILD FOR PRODUCTION
#ENV NODE_ENV=production
ENV NODE_ENV=development

# BUILD DIST FOLDER
RUN npm run lint && npm run build

# ------------------------------------------
# NGINX alpine distribution base image
FROM nginx:1-alpine

# COPY ROOT DIST FOLDER
COPY --from=build dist/ /usr/share/nginx/html

# ADD OUR NGINX configuration file to serve our HTML page
COPY default.conf /etc/nginx/conf.d/default.conf

# EXPOSE PORT 5000 (APPDAT STANDARD PORT)
EXPOSE 5000
