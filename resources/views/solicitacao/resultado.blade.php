<div class="card shadow-lg p-3 bg-white" style="border-radius: 0px 0px 10px 10px">

    <form id="form11" method="POST" action="{{route('solicitacao.resultado.criar')}}">
        @csrf
        <input type="hidden" name="planejamento_id" @if(!empty($planejamento)) value="{{$planejamento->id}}" @endif>
        <div class="row col-md-12" style=" @if(Auth::user()->tipo_usuario_id == 2) pointer-events: none @endif">
            <h3 class="subtitulo">Informações</h3>

            <div class="col-sm-6 mt-2">
                <label for="abate">Abate:<strong style="color: red">*</strong></label>
                <div class="row ml-1">
                    <div class="col-sm-2">
                        <input class="form-check-input" type="radio" name="abate_radio" id="abate_sim" value="true" @if(!empty($resultado) && $resultado->abate != null) checked @endif>
                        <label class="form-check-label" for="abate">Sim</label>
                    </div>
                    <div class="col-sm-2">
                        <input class="form-check-input" type="radio" name="abate_radio" id="abate_nao" value="false" @if(!empty($resultado) && $resultado->abate == null) checked @endif>
                        <label class="form-check-label" for="abate">
                            Não
                        </label>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 mt-2" id="destino_animal_abatido" style="display: none;">
                <label for="destino_animais">Destino dos Animais Abatidos:<strong style="color: red">*</strong></label>
                <textarea class="form-control @error('abate') is-invalid @enderror" name="abate" id="destino_animais" autocomplete="destino_animais" autofocus
                          required disabled>@if(!empty($resultado) && $resultado->abate != null){{$resultado->abate}} @else{{old('abate')}}@endif</textarea>
                @error('abate')
                <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
                @enderror
            </div>

            <div class="col-sm-12 mt-2">
                <label for="destino_animais">Destino dos animais sobreviventes após a conclusão do experimento / aula ou retirados no decorrer do experimento / aula:<strong style="color: red">*</strong></label>
                <textarea class="form-control @error('destino_animais') is-invalid @enderror" name="destino_animais" id="destino_animais" autocomplete="destino_animais" autofocus
                          required>@if(!empty($resultado) && $resultado->destino_animais != null){{$resultado->destino_animais}} @else{{old('destino_animais')}}@endif</textarea>
                @error('destino_animais')
                <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
                @enderror
            </div>

            <div class="col-sm-12 mt-2">
                <label for="outras_infos">Outras Informações Relevantes:<strong style="color: red">*</strong></label>
                <textarea class="form-control @error('outras_infos') is-invalid @enderror" name="outras_infos" id="outras_infos" autocomplete="outras_infos" autofocus
                          required>@if(!empty($resultado) && $resultado->outras_infos != null){{$resultado->outras_infos}} @else{{old('outras_infos')}}@endif</textarea>
                @error('outras_infos')
                <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
                @enderror
            </div>

            <div class="col-sm-12 mt-2">
                <label for="justificativa_metodos">Justificativa da não utilização de métodos alternativos e da necessidade do uso de animais:<strong style="color: red">*</strong></label>
                <textarea class="form-control @error('justificativa_metodos') is-invalid @enderror" name="justificativa_metodos" id="justificativa_metodos" autocomplete="justificativa_metodos"
                          autofocus required>@if(!empty($resultado) && $resultado->justificativa_metodos != null){{$resultado->justificativa_metodos}} @else{{old('justificativa_metodos')}}@endif</textarea>
                @error('justificativa_metodos')
                <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
                @enderror
            </div>

            <div class="col-sm-12 mt-2">
                <label for="resumo_procedimento">Resumo do procedimento (relatar todos os procedimentos com os animais):<strong style="color: red">*</strong></label>
                <textarea class="form-control @error('resumo_procedimento') is-invalid @enderror" name="resumo_procedimento" id="resumo_procedimento" autocomplete="resumo_procedimento" autofocus
                          required>@if(!empty($resultado) && $resultado->resumo_procedimento != null){{$resultado->resumo_procedimento}} @else{{old('resumo_procedimento')}}@endif</textarea>
                @error('resumo_procedimento')
                <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
                @enderror
            </div>


        </div>

        @include('component.botoes_new_form')

    </form>
</div>

<script>

    $(document).ready(function () {
        @if(!empty($resultado) && $resultado->abate != null)
            $("#abate_sim").attr('checked', true);
            $("#abate_sim").click();
        @else
            $("#abate_nao").attr('checked', true);
        @endif
    });

    $( "#abate_sim" ).click(function() {
        $("#destino_animal_abatido").show().find('input, textarea').prop('disabled', false);
    });
    $( "#abate_nao" ).click(function() {
        $("#destino_animal_abatido").hide().find('input, textarea').prop('disabled', true);
    });
</script>

