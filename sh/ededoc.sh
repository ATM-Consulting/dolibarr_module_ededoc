cd /root/
./mv-file.sh 

cd ./todo

ListeRep="$(find * -type d -prune)"   # liste des repertoires sans leurs sous-repertoires
for codeClient in ${ListeRep}; do
        cd ./${codeClient}/
        echo "Code client : ${codeClient}"

        ListeRep2="$(find * -type f -prune)"
        for filename in ${ListeRep2}; do
#               filename=$(basename $file)
                echo "----> fichier : ${filename} "

                /root/put-file.sh ${filename} ${codeClient}

        done 

done

