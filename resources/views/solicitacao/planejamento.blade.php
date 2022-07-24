<div class="card shadow-lg p-3 bg-white" style="border-radius: 10px">
    <div class="row">
        <div class="col-md-12">
            <h1 class="borda-bottom text-center titulo">Solicitação - Planejamento</h1>
        </div>
    </div>

    <div class="row">
        <h3 class="subtitulo">Planejamento Estatístico/Delineamento Experimental/Desenho Experimental</h3>
        <div class="col-sm-2">
            <label for="num_animais_grupo">Numero de animais por grupo:</label>
            <input class="form-control @error('num_animais_grupo') is-invalid @enderror" id="num_animais_grupo" type="number" name="num_animais_grupo" value="{{ old('num_animais_grupo') }}" required
                   autocomplete="num_animais_grupo" autofocus>
            @error('num_animais_grupo')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>

        <div class="col-sm-5">
            <label for="especificar_grupo">Especificar cada grupo (controle, tratado, utilizado para treinamento se for o caso):</label>
            <textarea class="form-control @error('especificar_grupo') is-invalid @enderror" id="especificar_grupo" name="especificar_grupo" required autocomplete="especificar_grupo"
                      autofocus>{{ old('especificar_grupo') }}</textarea>
            @error('especificar_grupo')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>

        <div class="col-sm-5">
            <label for="criterios">Quais critérios e/ou referências científicas foram utilizados para definir o tamanho da amostra: </label>
            <textarea class="form-control @error('criterios') is-invalid @enderror" id="criterios" name="criterios" required autocomplete="criterios"
                      autofocus>{{ old('criterios') }}</textarea>
            @error('criterios')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>

    </div>

    <div class="row mt-2">
        <div class="col-sm-12">
            <label for="anexo_formula">Apresentar, em anexo, a Fórmula Matemática que defina o n amostral. Apresentar o Desenho Experimental completo.
                Quando não for o caso, justifique.</label>
        </div>
        <div class="col-sm-6">
            <input class="form-control @error('anexo_formula') is-invalid @enderror" id="anexo_formula" type="file" name="anexo_formula" value="{{ old('anexo_formula') }}" required
                   autocomplete="anexo_formula" autofocus>
            @error('anexo_formula')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-sm-6">
            <label for="desc_materiais_metodos">Descrição de materiais e métodos:</label>
            <textarea class="form-control @error('desc_materiais_metodos') is-invalid @enderror" id="desc_materiais_metodos" name="desc_materiais_metodos" required
                      autocomplete="desc_materiais_metodos"
                      autofocus>{{ old('desc_materiais_metodos') }}</textarea>
            @error('desc_materiais_metodos')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>

        <div class="col-sm-6">
            <label for="analise_estatistica">Análise estatística</label>
            <textarea class="form-control @error('analise_estatistica') is-invalid @enderror" id="analise_estatistica" name="analise_estatistica" required autocomplete="analise_estatistica"
                      autofocus>{{ old('analise_estatistica') }}</textarea>
            @error('analise_estatistica')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-sm-12">
            <label for="outras_infos">Outras informações relevantes:</label>
            <textarea class="form-control @error('outras_infos') is-invalid @enderror" id="outras_infos" name="outras_infos" required autocomplete="outras_infos"
                      autofocus>{{ old('outras_infos') }}</textarea>
            @error('outras_infos')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>
    </div>

    <div class="row">
        <div class="row">
            <div class="col-auto">
                <h3 class="subtitulo">Grau de Invasividade</h3>
            </div>
            <div class="col-auto mt-3" style="margin-left: -20px">
                <span style="font-weight: lighter!important; font-size: 14px!important; color: dimgray; text-decoration: none!important;">(1,2,3 ou 4)</span>
            </div>
            <div class="col-auto mt-2" style="margin-left: -20px">
                <a target="_blank" class="btn btn-primary"
                   href="https://www2.dti.ufv.br/ceua/scripts/grau-invasividade.html">Consultar Grau</a>
            </div>
        </div>
        <div class="col-sm-12">
            <label for="grau_invasividade">Os materiais biológicos destes exemplares serão usados em outros projetos? Quais? Se já aprovado pela CEUA, mencionar o número do protocolo.</label>
            <textarea class="form-control @error('grau_invasividade') is-invalid @enderror" id="grau_invasividade" name="grau_invasividade" required autocomplete="grau_invasividade"
                      autofocus>{{ old('grau_invasividade') }}</textarea>
            @error('grau_invasividade')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>
    </div>

</div>
