url=`cat $1`

for i in $url
do
  echo $i\n;
  curl -IlL $i | grep "HTTP/1.1"
done;