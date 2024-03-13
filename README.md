# NDUS
## Docker
### Install docker Windows
Official [link](https://www.docker.com/products/docker-desktop/)

### Install docker Linux
```bash 
sudo apt install docker
```

### Running docker image

Firstly, build a container (it is convenient for windows, too)
```bash 
docker build . -t ndus
```

Then run the container (you can do this more easily in the desktop app)
```bash 
docker run --name ndus -p 8080:80 ndus
```

To stop (it takes a while) 
```bash 
docker stop ndus
```
