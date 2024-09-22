# Criando link simbólico no cmd (mklink)

## Caminho da pasta de origem
$origem = "C:\Caminho\Para\A\PastaOrigem"

## Caminho do destino onde o link será criado
$destino = "C:\Caminho\Para\A\PastaDestino"

## Cria o link simbólico usando o comando mklink com a opção /D para diretórios
cmd /c mklink /D "$destino" "$origem"

Write-Host "Link simbólico criado com sucesso de $origem para $destino"

# Criando link simbolico em Powershell

New-Item -Path C:\webserver\nginx\html\onde -ItemType SymbolicLink -Value c:\Users\Administrator\filipi\onde\web


https://stackoverflow.com/questions/894430/creating-hard-and-soft-links-using-powershell
