cd /root

filename=$1
codeclient=$2

cmd="curl --insecure --ftp-create-dirs -u $login:$password \"ftps://$host/${codeclient}/\" -T ./todo/${codeclient}/${filename} "

echo $cmd
eval $cmd 
mkdir "./done/${codeclient}/" 
mv -f "./todo/${codeclient}/${filename}" "./done/${codeclient}/${filename}"
