home=`dirname $0`
if [ home = "" ]
  then home = "."
fi
for srcfile in $home/../tcp5/*.xml
do
  # destfile=$home/../dest/`basename $srcfile`
  # echo $destfile
  xsltproc -o $srcfile $home/tcfront2p5.xsl $srcfile
done
