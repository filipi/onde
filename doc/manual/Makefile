all:	manual.pdf

manual.pdf:	manual.aux
	pdflatex --interaction batchmode manual
	pdflatex --interaction batchmode manual

manual.aux:	manual.tex
	pdflatex --interaction batchmode manual

manual.html:	manual.tex
	htlatex manual.tex

clean:
	@rm -rfv *~ *.toc *.lof *.log *.aux
	@rm -rfv rm -rf *.4* *.tmp *.xref *.css *.lg *.dvi *.html *.idv *.png *.out

