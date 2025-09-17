# Guide

- Open layout.html by chrome browser.
- Check style and layout.
- Save images to ./kl folder
- Run s3 cli to sync.

# copy to s3

```bash
aws s3 sync ./images s3://metafox-dev/kl --acl public-read
```

check result
https://metafox-dev.s3.amazonaws.com/kl/default.jpeg
