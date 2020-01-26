curl --request POST \
  --url https://127.0.0.1:8000/upload/file \
  --header 'content-type: multipart/form-data;' \
  --form "api_key=123123" \
  --form "hubs[]=hub1" \
  --form "tags[]=tag1" \
  --form "files[]=@/Users/slote/Desktop/spider.png" \
  --output test.html