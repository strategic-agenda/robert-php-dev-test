# Use a Node image to build the React app
FROM node:latest as build

# Set working directory
WORKDIR /app

# Copy package.json and install dependencies
COPY package.json package-lock.json ./
RUN npm install

# Copy the rest of your app's source code
COPY . ./

# Build the app
RUN #npm run build

# Use Nginx to serve the React app
FROM nginx:alpine

# Copy built assets from build stage
#COPY --from=build /app/build /usr/share/nginx/html

# Expose port 80
EXPOSE 80
