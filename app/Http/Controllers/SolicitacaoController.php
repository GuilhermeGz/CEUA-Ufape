<?php

namespace App\Http\Controllers;

use App\Models\AvaliacaoIndividual;
use Illuminate\Support\Facades\Validator;
use App\Mail\SendSolicitacaoStatus;
use App\Mail\SendSolicitacaoReprovada;
use App\Models\Avaliacao;
use App\Models\Colaborador;
use App\Models\CondicoesAnimal;
use App\Models\Contato;
use App\Models\DadosComplementares;
use App\Models\Eutanasia;
use App\Models\Instituicao;
use App\Models\Licenca;
use App\Models\ModeloAnimal;
use App\Models\Operacao;
use App\Models\Perfil;
use App\Models\Planejamento;
use App\Models\Procedimento;
use App\Models\Responsavel;
use App\Models\Resultado;
use App\Models\Solicitacao;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Phalcon\Forms\Element\Date;

class SolicitacaoController extends Controller
{

    public function index_solicitacao($solicitacao_id)
    {
        $solicitacao = Solicitacao::find($solicitacao_id);
        $instituicaos = Instituicao::all();

        if(Auth::user()->tipo_usuario_id == 3 && $solicitacao->status == 'avaliado' && $solicitacao->avaliacao->first()->status == 'aprovadaPendencia'){
            $avaliacao = Avaliacao::where('solicitacao_id', $solicitacao_id)->where('user_id', $solicitacao->avaliacao->first()->user_id)->first();


            $avaliacaoDadosComp = AvaliacaoIndividual::where('avaliacao_id', $avaliacao->id)->where('dados_complementares_id', $solicitacao->dadosComplementares->id)->first();
            $avaliacaoDadosini = AvaliacaoIndividual::where('avaliacao_id', $avaliacao->id)->where('solicitacao_id', $solicitacao->id)->first();
            $avaliacaoResponsavel = AvaliacaoIndividual::where('avaliacao_id', $avaliacao->id)->where('responsavel_id', $solicitacao->responsavel->id)->first();
            $avaliacaoColaborador = AvaliacaoIndividual::where('avaliacao_id', $avaliacao->id)->where('tipo', 2)->first();

            return view('solicitacao.index', compact('solicitacao',
                'instituicaos','avaliacaoDadosComp', 'avaliacaoDadosini', 'avaliacaoResponsavel', 'avaliacaoColaborador','avaliacao'));

        }

        return view('solicitacao.index', compact('solicitacao', 'instituicaos'));
    }

    public function avaliarSolicitacao($solicitacao_id)
    {
        $solicitacao = Solicitacao::find($solicitacao_id);
        $instituicaos = Instituicao::all();

        $disabled = true;
        $responsavel = $solicitacao->responsavel;
        $colaboradores = $solicitacao->responsavel->colaboradores;
        $modelo_animais = $solicitacao->modeloAnimal;
        $solicitacao->avaliador_atual_id = Auth::user()->id;
        $solicitacao->update();
        $avaliacao = Avaliacao::where('solicitacao_id', $solicitacao_id)->where('user_id', Auth::user()->id)->first();


        $avaliacaoDadosComp = AvaliacaoIndividual::where('avaliacao_id', $avaliacao->id)->where('dados_complementares_id', $solicitacao->dadosComplementares->id)->first();
        $avaliacaoDadosini = AvaliacaoIndividual::where('avaliacao_id', $avaliacao->id)->where('solicitacao_id', $solicitacao->id)->first();
        $avaliacaoResponsavel = AvaliacaoIndividual::where('avaliacao_id', $avaliacao->id)->where('responsavel_id', $responsavel->id)->first();
        $avaliacaoColaborador = AvaliacaoIndividual::where('avaliacao_id', $avaliacao->id)->where('tipo', 2)->first();

        return view('solicitacao.index', compact('disabled', 'solicitacao',
            'instituicaos', 'responsavel', 'colaboradores', 'modelo_animais', 'avaliacao',
            'avaliacaoDadosComp', 'avaliacaoDadosini', 'avaliacaoResponsavel', 'avaliacaoColaborador'));

    }

    public function index_solicitante()
    {
        $solicitante = Auth::user();
        $solicitacoes = Solicitacao::where('user_id', $solicitante->id)->orderByDesc("created_at")->get();
        return view('solicitante.minhas_solicitacoes', compact('solicitacoes'));
    }

    public function index_avaliador()
    {
        $avaliacoes = Avaliacao::where('user_id', Auth::user()->id)->get();
        $horario = Carbon::now('UTC')->toDateTime();
        return view('avaliador.minhas_avaliacoes', compact('avaliacoes', 'horario'));
    }

    public function inicio(Request $request)
    {
        $solicitacao = new Solicitacao();
        $solicitacao->tipo = $request->tipo;
        $solicitacao->user_id = Auth::user()->id;
    

        $solicitacao->save();

        return redirect(route('solicitacao.index', ['solicitacao_id' => $solicitacao->id]));
    }

    public function criar(Request $request)
    {
        Validator::make($request->all(), Solicitacao::$rules, Solicitacao::$messages)->validate();

        $solicitacao = Solicitacao::find($request->solicitacao_id);
        $solicitacao->titulo_pt = $request->titulo_pt;
        $solicitacao->titulo_en = $request->titulo_en;
        $solicitacao->inicio = $request->inicio;
        $solicitacao->fim = $request->fim;
        $solicitacao->area_conhecimento = $request->area_conhecimento;
        if (isset($request->outra_area_conhecimento))
            $solicitacao->outra_area_conhecimento = $request->outra_area_conhecimento;
        $solicitacao->update();

        return redirect(route('solicitacao.index', ['solicitacao_id' => $request->solicitacao_id]));
    }

    public function criar_responsavel(Request $request)
    {
        Validator::make($request->all(), Responsavel::$rules, Responsavel::$messages)->validate();
        Validator::make($request->all(), Contato::$rules, Contato::$messages)->validate();

        $solicitacao = Solicitacao::find($request->solicitacao_id);

        if (isset($solicitacao->responsavel)) {
            $responsavel = $solicitacao->responsavel;
        } else {
            $responsavel = new Responsavel();
        }

        if (($request->hasFile('experiencia_previa') && $request->file('experiencia_previa')->isValid())) {
            $anexo = $request->experiencia_previa->extension();
            $nomeAnexo = "experiencia_" . $solicitacao->id . date('Ymd') . date('His') . '.' . $anexo;
            if ($responsavel->experiencia_revia != null) {
                $nomeAnexo = $responsavel->experiencia_previa;
            }
            $request->experiencia_previa->storeAs('experiencias/', $nomeAnexo);
            $request->experiencia_previa = $nomeAnexo;
        }

        if (($request->hasFile('treinamento') && $request->file('treinamento')->isValid())) {
            $anexo = $request->treinamento->extension();
            $nomeAnexo = "treinamento_" . $solicitacao->id . date('Ymd') . date('His') . '.' . $anexo;
            if ($responsavel->treinamento != null) {
                $nomeAnexo = $responsavel->treinamento;
            }
            $request->treinamento->storeAs('treinamentos/', $nomeAnexo);
            $request->treinamento = $nomeAnexo;
        }


        $responsavel->solicitacao_id = $request->solicitacao_id;
        $responsavel->nome = $request->nome;
        $responsavel->departamento_id = $request->departamento_id;
        if ($request->experiencia_previa == null && $responsavel->experiencia_previa != null)
            $request->experiencia_previa = $responsavel->experiencia_previa;
        if ($request->experiencia_previa_radio == "false")
            $request->experiencia_previa = null;
        $responsavel->experiencia_previa = $request->experiencia_previa;
        $responsavel->vinculo_instituicao = $request->vinculo_instituicao;
        $responsavel->grau_escolaridade = $request->grau_escolaridade;
        if ($request->treinamento == null && $responsavel->treinamento != null)
            $request->treinamento = $responsavel->treinamento;
        if ($request->treinamento_radio == "false")
            $request->treinamento = null;
        $responsavel->treinamento = $request->treinamento;


        if (isset($solicitacao->responsavel)) {
            $responsavel->update();
        } else {
            $responsavel->save();
        }

        if (isset($responsavel->contato)) {
            $contato = $responsavel->contato;
        } else {
            $contato = new Contato();
        }

        $contato->email = $request->email;
        $contato->telefone = $request->telefone;
        $contato->responsavel_id = $responsavel->id;

        if (isset($responsavel->contato)) {
            $contato->update();
        } else {
            $contato->save();
        }

        return redirect(route('solicitacao.index', ['solicitacao_id' => $request->solicitacao_id]));

    }

    public function criar_colaborador(Request $request)
    {
        //Validator::make($request->all(), Colaborador::$rules, Colaborador::$messages)->validate();
        $solicitacao = Solicitacao::find($request->solicitacao_id);
        $listaColab = [];

        if (isset($request->colaborador)) {
            foreach ($request->colaborador as $colab) {
                if ($colab['colab_id'] != null) {
                    $colaborador = Colaborador::find($colab['colab_id']);
                } else {
                    $colaborador = new Colaborador();
                }
                $colaborador->nome = $colab['nome'];
                $colaborador->instituicao_id = $colab['instituicao_id'];
                $colaborador->grau_escolaridade = $colab['grau_escolaridade'];
                $colaborador->nivel_academico = $colab['nivel_academico'];
                $colaborador->experiencia_previa = $colab['experiencia_previa'];
                $colaborador->treinamento = $colab['treinamento'];
                $colaborador->responsavel_id = $solicitacao->responsavel->id;

                if ($colab['colab_id'] != null) {
                    $colaborador->update();
                    $contato = $colaborador->contato;
                } else {
                    $colaborador->save();
                    $contato = new Contato();;
                }

                array_push($listaColab, $colaborador->id);
                $contato->email = $colab['email'];
                $contato->telefone = $colab['telefone'];
                $contato->colaborador_id = $colaborador->id;

                if ($colab['colab_id'] != null) {
                    $contato->update();
                } else {
                    $contato->save();
                }
            }
        }
        //Deletar colaboradores não fornecidos no formulário
        Colaborador::where('responsavel_id', $solicitacao->responsavel->id)->whereNotIn('id', $listaColab)->delete();

        $solicitacao->update();
        return redirect(route('solicitacao.index', ['solicitacao_id' => $request->solicitacao_id]));
    }

    public function criar_solicitacao_fim(Request $request)
    {

        Validator::make($request->all(), DadosComplementares::$rules, DadosComplementares::$messages)->validate();
        $solicitacao = Solicitacao::find($request->solicitacao_id);

        if (isset($solicitacao->dadosComplementares)) {
            $solicitacao->dadosComplementares->update($request->all());
        } else {
            DadosComplementares::create($request->all());
        }

        $solicitacao->status = null;
        $solicitacao->update();
        return redirect(route('solicitacao.index', ['solicitacao_id' => $solicitacao->id]));
    }

    public function criar_modelo_animal(Request $request)
    {
        Validator::make($request->all(), array_merge(ModeloAnimal::$rules, Perfil::$rules), array_merge(ModeloAnimal::$messages, Perfil::$messages))->validateWithBag('modelo');

        $data = $request->all();
        if (($request->hasFile('termo_consentimento') && $request->file('termo_consentimento')->isValid())) {
            $anexo = $request->termo_consentimento->extension();
            $nomeAnexo = "tcle_" . $request->solicitacao_id . date('Ymd') . date('His') . '.' . $anexo;
            $request->termo_consentimento->storeAs('termos/', $nomeAnexo);
            $data['termo_consentimento'] = $nomeAnexo;
        }

        $modelo_animal = ModeloAnimal::create($data);


        $perfil = new Perfil();
        $perfil->grupo_animal = $request->grupo_animal;
        $perfil->linhagem = $request->linhagem;
        $perfil->idade = $request->idade;
        $perfil->periodo = $request->periodo;
        $perfil->peso = $request->peso;
        $perfil->quantidade = $request->quantidade;
        $perfil->machos = $request->machos;
        $perfil->femeas = $request->femeas;
        $perfil->total = $request->quantidade;
        $perfil->modelo_animal_id = $modelo_animal->id;
        $perfil->save();
        return redirect(route('solicitacao.index', ['solicitacao_id' => $request->solicitacao_id]))->with('success', 'Modelo Animal Criado com Sucesso!');
    }

    public function atualizar_modelo_animal(Request $request)
    {
        $modelo_animal = ModeloAnimal::find($request->modelo_animal_id);

        if (($request->hasFile('termo_consentimento') && $request->file('termo_consentimento')->isValid())) {
            $nomeAnexo = $modelo_animal->termo_consentimento;
            $request->termo_consentimento->storeAs('termos/', $nomeAnexo);
            $request->termo_consentimento = $nomeAnexo;
        }

        $modelo_animal->update($request->all());

        $perfil = $modelo_animal->perfil;
        $perfil->grupo_animal = $request->grupo_animal;
        $perfil->linhagem = $request->linhagem;
        $perfil->idade = $request->idade;
        $perfil->peso = $request->peso;
        $perfil->quantidade = $request->quantidade;
        $perfil->machos = $request->machos;
        $perfil->femeas = $request->femeas;
        $perfil->total = $request->quantidade;
        $perfil->modelo_animal_id = $modelo_animal->id;
        $perfil->update();

        return redirect(route('solicitacao.planejamento.index', ['modelo_animal_id' => $request->modelo_animal_id]))->with('success', 'Modelo Animal Atualizado com Sucesso!');
    }

    public function deletar_modelo_animal($id)
    {

        ModeloAnimal::find($id)->delete();
        return redirect()->back()->with('success', 'Modelo Animal removido com sucesso!');

    }

    public function criar_perfil(Request $request)
    {
        $solicitacao = Solicitacao::find($request->solicitacao_id);
        $modelo_animal = ModeloAnimal::where('solicitacao_id', $solicitacao->id)->first();

        if (isset($modelo_animal->perfil)) {
            $perfil = $modelo_animal->perfil;
        } else {
            $perfil = new Perfil();
        }

        $perfil->grupo_animal = $request->grupo_animal;
        $perfil->linhagem = $request->linhagem;
        $perfil->idade = $request->idade;
        $perfil->periodo = $request->periodo;
        $perfil->peso = $request->peso;
        $perfil->machos = $request->machos;
        $perfil->femeas = $request->femeas;
        $perfil->quantidade = $request->quantidade;
        $perfil->modelo_animal_id = $modelo_animal->id;
        $perfil->total = $request->quantidade; //Verificar depois com o pessoal da CEUA

        if (isset($modelo_animal->perfil)) {
            $perfil->update();
        } else {
            $perfil->save();
        }

        $solicitacao->update();

        return redirect(route('solicitacao.form', ['solicitacao_id' => $request->solicitacao_id]));
    }

    public function downloadFormula($planejamento_id)
    {
        $planejamento = Planejamento::find($planejamento_id);
        return Storage::download('formulas/' . $planejamento->anexo_formula);
    }

    public function downloadTermo($modelo_animal_id)
    {
        $modelo_animal = ModeloAnimal::find($modelo_animal_id);
        return Storage::download('termos/' . $modelo_animal->termo_consentimento);
    }

    public function downloadTreinamento($responsavel_id)
    {
        $responsavel = Responsavel::find($responsavel_id);
        return Storage::download('treinamentos/' . $responsavel->treinamento);
    }

    public function downloadExperiencia($responsavel_id)
    {
        $responsavel = Responsavel::find($responsavel_id);
        return Storage::download('experiencias/' . $responsavel->experiencia_previa);
    }

    public function index_planejamento($modelo_animal_id)
    {
        $modelo_animal = ModeloAnimal::find($modelo_animal_id);
        $planejamento = Planejamento::where('modelo_animal_id', $modelo_animal_id)->first();
        $solicitacao = Solicitacao::find($modelo_animal->solicitacao_id);

        //Componentes que requerem ter Planejamento
        if ($planejamento != null) {
            $condicoes_animal = CondicoesAnimal::where('planejamento_id', $planejamento->id)->first();
            $procedimento = Procedimento::where('planejamento_id', $planejamento->id)->first();
            $operacao = Operacao::where('planejamento_id', $planejamento->id)->first();
            $eutanasia = Eutanasia::where('planejamento_id', $planejamento->id)->first();
            $resultado = Resultado::where('planejamento_id', $planejamento->id)->first();
        } else {
            $condicoes_animal = null;
            $procedimento = null;
            $operacao = null;
            $eutanasia = null;
            $resultado = null;
        }

        if(Auth::user()->tipo_usuario_id == 3 && $solicitacao->status == 'avaliado' && $solicitacao->avaliacao->first()->status == 'aprovadaPendencia'){
            $avaliacao = Avaliacao::where('solicitacao_id', $solicitacao->id)->where('user_id', $solicitacao->avaliacao->first()->user_id)->first();
            // Avaliações Individuais
            $avaliacaoPlanejamento = AvaliacaoIndividual::where('avaliacao_id', $avaliacao->id)->where('planejamento_id', $planejamento->id)->first();
            $avaliacaoCondicoesAnimal = AvaliacaoIndividual::where('avaliacao_id', $avaliacao->id)->where('condicoes_animal_id', $condicoes_animal->id)->first();
            $avaliacaoProcedimento = AvaliacaoIndividual::where('avaliacao_id', $avaliacao->id)->where('procedimento_id', $procedimento->id)->first();
            $avaliacaoOperacao = AvaliacaoIndividual::where('avaliacao_id', $avaliacao->id)->where('operacao_id', $operacao->id)->first();
            $avaliacaoEutanasia = AvaliacaoIndividual::where('avaliacao_id', $avaliacao->id)->where('eutanasia_id', $eutanasia->id)->first();
            $avaliacaoResultado = AvaliacaoIndividual::where('avaliacao_id', $avaliacao->id)->where('resultado_id', $resultado->id)->first();
            $avaliacaoModeloAnimal = AvaliacaoIndividual::where('avaliacao_id', $avaliacao->id)->where('modelo_animal_id', $modelo_animal->id)->first();

            return view('planejamento.index',
                compact('modelo_animal', 'planejamento', 'solicitacao', 'condicoes_animal', 'procedimento', 'operacao', 'eutanasia', 'resultado', 'avaliacao',
                    'avaliacaoPlanejamento', 'avaliacaoCondicoesAnimal', 'avaliacaoProcedimento', 'avaliacaoOperacao',
                    'avaliacaoEutanasia', 'avaliacaoResultado', 'avaliacaoModeloAnimal'));
        }

        return view('planejamento.index',
            compact('modelo_animal', 'planejamento', 'solicitacao', 'condicoes_animal', 'procedimento', 'operacao', 'eutanasia', 'resultado'));
    }

    public function avaliarPlanejamento($modelo_animal_id)
    {
        $modelo_animal = ModeloAnimal::find($modelo_animal_id);
        $planejamento = Planejamento::where('modelo_animal_id', $modelo_animal_id)->first();
        $solicitacao = Solicitacao::find($modelo_animal->solicitacao_id);


        $condicoes_animal = CondicoesAnimal::where('planejamento_id', $planejamento->id)->first();
        $procedimento = Procedimento::where('planejamento_id', $planejamento->id)->first();
        $operacao = Operacao::where('planejamento_id', $planejamento->id)->first();
        $eutanasia = Eutanasia::where('planejamento_id', $planejamento->id)->first();
        $resultado = Resultado::where('planejamento_id', $planejamento->id)->first();

        $avaliacao = Avaliacao::where('solicitacao_id', $solicitacao->id)->where('user_id', Auth::user()->id)->first();
        // Avaliações Individuais
        $avaliacaoPlanejamento = AvaliacaoIndividual::where('avaliacao_id', $avaliacao->id)->where('planejamento_id', $planejamento->id)->first();
        $avaliacaoCondicoesAnimal = AvaliacaoIndividual::where('avaliacao_id', $avaliacao->id)->where('condicoes_animal_id', $condicoes_animal->id)->first();
        $avaliacaoProcedimento = AvaliacaoIndividual::where('avaliacao_id', $avaliacao->id)->where('procedimento_id', $procedimento->id)->first();
        $avaliacaoOperacao = AvaliacaoIndividual::where('avaliacao_id', $avaliacao->id)->where('operacao_id', $operacao->id)->first();
        $avaliacaoEutanasia = AvaliacaoIndividual::where('avaliacao_id', $avaliacao->id)->where('eutanasia_id', $eutanasia->id)->first();
        $avaliacaoResultado = AvaliacaoIndividual::where('avaliacao_id', $avaliacao->id)->where('resultado_id', $resultado->id)->first();
        $avaliacaoModeloAnimal = AvaliacaoIndividual::where('avaliacao_id', $avaliacao->id)->where('modelo_animal_id', $modelo_animal->id)->first();


        return view('planejamento.index',
            compact('modelo_animal', 'planejamento', 'solicitacao', 'condicoes_animal', 'procedimento', 'operacao', 'eutanasia', 'resultado', 'avaliacao',
                'avaliacaoPlanejamento', 'avaliacaoCondicoesAnimal', 'avaliacaoProcedimento', 'avaliacaoOperacao',
                'avaliacaoEutanasia', 'avaliacaoResultado', 'avaliacaoModeloAnimal'));
    }

    public function criar_planejamento(Request $request)
    {
        Validator::make($request->all(), Planejamento::$rules, Planejamento::$messages)->validate();
        $modelo_animal = ModeloAnimal::find($request->modelo_animal_id);
        if (isset($modelo_animal->planejamento)) {
            $planejamento = $modelo_animal->planejamento;

            if (($request->hasFile('anexo_formula') && $request->file('anexo_formula')->isValid())) {
                if ($planejamento->anexo != null) {
                    $nomeAnexo = $planejamento->anexo_formula;
                } else {
                    $anexo = $request->anexo_formula->extension();
                    $nomeAnexo = "formula_" . date('Ymd') . date('His') . '.' . $anexo;
                }
                $planejamento->anexo_formula = $nomeAnexo;
                $request->anexo_formula->storeAs('formulas/', $nomeAnexo);
            }

        } else {
            $planejamento = new Planejamento();

            if (($request->hasFile('anexo_formula') && $request->file('anexo_formula')->isValid())) {

                $anexo = $request->anexo_formula->extension();
                $nomeAnexo = "formula_" . date('Ymd') . date('His') . '.' . $anexo;
                $planejamento->anexo_formula = $nomeAnexo;
                $request->anexo_formula->storeAs('formulas/', $nomeAnexo);
                $request->anexo_formula = $nomeAnexo;
            }
        }


        if (isset($modelo_animal->planejamento)) {
            $planejamento = $modelo_animal->planejamento;
        } else {
            $planejamento = new Planejamento();
        }
        $planejamento->modelo_animal_id = $modelo_animal->id;
        $planejamento->num_animais_grupo = $request->num_animais_grupo;
        $planejamento->especificar_grupo = $request->especificar_grupo;
        $planejamento->criterios = $request->criterios;
        $planejamento->desc_materiais_metodos = $request->desc_materiais_metodos;
        $planejamento->analise_estatistica = $request->analise_estatistica;
        $planejamento->outras_infos = $request->outras_infos;
        $planejamento->grau_invasividade = $request->grau_invasividade;

        if (isset($modelo_animal->planejamento)) {
            $planejamento->update();
        } else {
            $planejamento->save();
        }
        return redirect(route('solicitacao.planejamento.index', ['modelo_animal_id' => $planejamento->modelo_animal->id]));
    }

    public function criar_condicoes_animal(Request $request)
    {
        Validator::make($request->all(), CondicoesAnimal::$rules, CondicoesAnimal::$messages)->validate();

        $planejamento = Planejamento::find($request->planejamento_id);

        if (isset($planejamento->condicoesAnimal)) {
            $condicoes_animal = $planejamento->condicoesAnimal;
        } else {
            $condicoes_animal = new CondicoesAnimal();
            $condicoes_animal->planejamento_id = $planejamento->id;
        }

        $condicoes_animal->condicoes_particulares = $request->condicoes_particulares;
        $condicoes_animal->local = $request->local;
        $condicoes_animal->ambiente_alojamento = $request->ambiente_alojamento;
        $condicoes_animal->tipo_cama = $request->tipo_cama;
        $condicoes_animal->num_animais_ambiente = $request->num_animais_ambiente;
        $condicoes_animal->dimensoes_ambiente = $request->dimensoes_ambiente;
        $condicoes_animal->periodo = $request->periodo;
        $condicoes_animal->profissional_responsavel = $request->profissional_responsavel;
        $condicoes_animal->email_responsavel = $request->email_responsavel;

        if (isset($planejamento->condicoesAnimal)) {
            $condicoes_animal->update();
        } else {
            $condicoes_animal->save();
        }

        return redirect(route('solicitacao.planejamento.index', ['modelo_animal_id' => $planejamento->modelo_animal->id]));
    }

    public function criar_procedimento(Request $request)
    {
        Validator::make($request->all(), Procedimento::$rules, Procedimento::$messages)->validate();

        $planejamento = Planejamento::find($request->planejamento_id);

        if (isset($planejamento->procedimento)) {
            $procedimento = $planejamento->procedimento;
            $procedimento->update($request->all());
        } else {
            $procedimento = new Procedimento();
            $procedimento->planejamento_id = $planejamento->id;
            $procedimento->create($request->all());
        }

        return redirect(route('solicitacao.planejamento.index', ['modelo_animal_id' => $planejamento->modelo_animal->id]));
    }

    public function criar_operacao(Request $request)
    {
        Validator::make($request->all(), Operacao::$rules, Operacao::$messages)->validate();

        $planejamento = Planejamento::find($request->planejamento_id);


        if (isset($planejamento->operacao)) {
            $operacao = $planejamento->operacao;
        } else {
            $operacao = new Operacao();
            $operacao->planejamento_id = $planejamento->id;
        }

        if ($request->cirurgia != "true") {
            $operacao->observacao_recuperacao = null;
            $operacao->outros_cuidados_recuperacao = null;
            $operacao->analgesia_recuperacao = null;
        } else {
            $operacao->observacao_recuperacao = $request->observacao_recuperacao;
            $operacao->outros_cuidados_recuperacao = $request->outros_cuidados_recuperacao;
            $operacao->analgesia_recuperacao = $request->analgesia_recuperacao;
        }

        if (isset($planejamento->operacao)) {
            $operacao->update();
        } else {
            $operacao->save();
        }


        return redirect(route('solicitacao.planejamento.index', ['modelo_animal_id' => $planejamento->modelo_animal->id]));
    }

    public
    function criar_eutanasia(Request $request)
    {
        Validator::make($request->all(), Eutanasia::$rules, Eutanasia::$messages)->validate();

        $planejamento = Planejamento::find($request->planejamento_id);

        if (isset($planejamento->eutanasia)) {
            $eutanasia = $planejamento->eutanasia;
        } else {
            $eutanasia = new Eutanasia();
            $eutanasia->planejamento_id = $planejamento->id;
        }

        if ($request->eutanasia == "true") {
            $eutanasia->descricao = $request->descricao;
            $eutanasia->metodo = $request->metodo;
            $eutanasia->justificativa_metodo = $request->justificativa_metodo;
        } else {
            $eutanasia->descricao = null;
            $eutanasia->metodo = null;
            $eutanasia->justificativa_metodo = null;
        }

        $eutanasia->destino = $request->destino;
        $eutanasia->descarte = $request->descarte;

        if (isset($planejamento->eutanasia)) {
            $eutanasia->update();
        } else {
            $eutanasia->save();
        }

        return redirect(route('solicitacao.planejamento.index', ['modelo_animal_id' => $planejamento->modelo_animal->id]));
    }

    public
    function criar_resultado(Request $request)
    {
        Validator::make($request->all(), Resultado::$rules, Resultado::$messages)->validate();

        $planejamento = Planejamento::find($request->planejamento_id);

        if (isset($planejamento->resultado)) {
            $resultado = $planejamento->resultado;
            $resultado->update($request->all());
        } else {
            $resultado = new Resultado();
            $resultado->planejamento_id = $planejamento->id;
            $resultado->create($request->all());
        }

        /* Envio de Email ao administrador
        $admins = User::where('tipo_usuario_id', 1)->get();
        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new SendNotificacaoSolicitacao($admin));
        }
        */

        return redirect(route('solicitacao.planejamento.index', ['modelo_animal_id' => $planejamento->modelo_animal->id]));
    }

    public
    function index_admin()
    {
        $solicitacoes = Solicitacao::where('status', '!=', 'avaliado')->get();
        $avaliadores = User::where('tipo_usuario_id', '2')->get();
        return view('admin.solicitacoes', compact('solicitacoes', 'avaliadores'));
    }

    public
    function concluir($solicitacao_id)
    {
        $concluir = true;
        $solicitacao = Solicitacao::where('id', $solicitacao_id)->where('user_id', Auth::user()->id)->first();
        foreach ($solicitacao->modelosAnimais as $modelo) {
            if (!isset($modelo->planejamento) && !isset($modelo->planejamento->operacao) && !isset($modelo->planejamento->eutanasia)
                && !isset($modelo->planejamento->resultado) && !isset($modelo->planejamento->procedimento) && !isset($modelo->planejamento->condicoesAnimal)) {
                $concluir = false;
            }
        }

        if($concluir == false){
            return redirect()->back()->with('fail', 'É necessário preencher todas as informações obrigatórias!');
        }
        if ($solicitacao == null) {
            return redirect()->back()->with('fail', 'Solicitação não encontrada');
        }
        $solicitacao->status = 'nao_avaliado';
        $solicitacao->update();
        return redirect(route('solicitacao.solicitante.index'))->with(['success' => 'Solicitação concluída com sucesso!']);
    }
}
