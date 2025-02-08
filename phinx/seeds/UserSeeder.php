<?php declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class UserSeeder extends AbstractSeed
{
    public function run(): void
    {
        $users = $this->table('users');
        $users->truncate();

        $data = [
            [
                'username' => 'johndoe',
                'name' => 'John Doe',
                'email' => 'johndoe@example.com',
                'password' => 'senha123',
                'bio' => 'Amo programar e aprender novas tecnologias.'
            ],
            [
                'username' => 'janedoe',
                'name' => 'Jane Doe',
                'email' => 'janedoe@example.com',
                'password' => 'senha456',
                'bio' => 'Desenvolvedora full-stack apaixonada por design.'
            ],
            [
                'username' => 'alice_smith',
                'name' => 'Alice Smith',
                'email' => 'alice.smith@example.com',
                'password' => 'alice@123',
                'bio' => 'Criativa e curiosa, sempre em busca de novos desafios.'
            ],
            [
                'username' => 'bob_jones',
                'name' => 'Bob Jones',
                'email' => 'bob.jones@example.com',
                'password' => 'bob@2025',
                'bio' => 'Entusiasta de software livre e segurança digital.'
            ],
            [
                'username' => 'lucas_pereira',
                'name' => 'Lucas Pereira',
                'email' => 'lucas.pereira@example.com',
                'password' => 'lucas123',
                'bio' => 'Apaixonado por inteligência artificial e machine learning.'
            ],
            [
                'username' => 'mariana_oliveira',
                'name' => 'Mariana Oliveira',
                'email' => 'mariana.oliveira@example.com',
                'password' => 'mariana@456',
                'bio' => 'Desenvolvedora front-end com foco em usabilidade.'
            ],
            [
                'username' => 'pedro_fernandes',
                'name' => 'Pedro Fernandes',
                'email' => 'pedro.fernandes@example.com',
                'password' => 'pedro789',
                'bio' => 'Gosto de explorar novos frameworks e ferramentas.'
            ],
            [
                'username' => 'claudia_souza',
                'name' => 'Claudia Souza',
                'email' => 'claudia.souza@example.com',
                'password' => 'claudia@123',
                'bio' => 'Criadora de conteúdo e entusiasta de UX/UI.'
            ],
            [
                'username' => 'roberto_gomes',
                'name' => 'Roberto Gomes',
                'email' => 'roberto.gomes@example.com',
                'password' => 'roberto@2025',
                'bio' => 'Desenvolvedor back-end e fã de tecnologias escaláveis.'
            ],
            [
                'username' => 'ana_martins',
                'name' => 'Ana Martins',
                'email' => 'ana.martins@example.com',
                'password' => 'ana12345',
                'bio' => 'Programadora apaixonada por desafios complexos.'
            ],
            [
                'username' => 'gustavo_costa',
                'name' => 'Gustavo Costa',
                'email' => 'gustavo.costa@example.com',
                'password' => 'gustavo@987',
                'bio' => 'Especialista em análise de dados e visualização.'
            ],
            [
                'username' => 'beatriz_silva',
                'name' => 'Beatriz Silva',
                'email' => 'beatriz.silva@example.com',
                'password' => 'beatriz@321',
                'bio' => 'Desenvolvedora web com interesse em acessibilidade.'
            ],
            [
                'username' => 'daniel_tavares',
                'name' => 'Daniel Tavares',
                'email' => 'daniel.tavares@example.com',
                'password' => 'daniel2025',
                'bio' => 'Estudante de ciência da computação e amante de algoritmos.'
            ],
            [
                'username' => 'laura_rosa',
                'name' => 'Laura Rosa',
                'email' => 'laura.rosa@example.com',
                'password' => 'laura@321',
                'bio' => 'Designer gráfica que adora trabalhar com dados.'
            ],
            [
                'username' => 'eduardo_correia',
                'name' => 'Eduardo Correia',
                'email' => 'eduardo.correia@example.com',
                'password' => 'eduardo@777',
                'bio' => 'Focado em otimização de processos e qualidade de código.'
            ],
            [
                'username' => 'karla_oliveira',
                'name' => 'Karla Oliveira',
                'email' => 'karla.oliveira@example.com',
                'password' => 'karla123',
                'bio' => 'Interessada em desenvolvimento de aplicações móveis.'
            ],
            [
                'username' => 'felipe_barbosa',
                'name' => 'Felipe Barbosa',
                'email' => 'felipe.barbosa@example.com',
                'password' => 'felipe@123',
                'bio' => 'Entusiasta de cloud computing e DevOps.'
            ],
            [
                'username' => 'sandra_lima',
                'name' => 'Sandra Lima',
                'email' => 'sandra.lima@example.com',
                'password' => 'sandra@456',
                'bio' => 'Programadora front-end com foco em performance.'
            ],
            [
                'username' => 'gustavo_ribeiro',
                'name' => 'Gustavo Ribeiro',
                'email' => 'gustavo.ribeiro@example.com',
                'password' => 'gustavo@789',
                'bio' => 'Aficionado por automação de testes e QA.'
            ],
            [
                'username' => 'patricia_santos',
                'name' => 'Patricia Santos',
                'email' => 'patricia.santos@example.com',
                'password' => 'patricia@2025',
                'bio' => 'Especialista em segurança cibernética e criptografia.'
            ]
        ];

        foreach ($data as $record) {
            $record['password'] = password_hash($record['password'], PASSWORD_BCRYPT);
            $users->insert($record);
        }

        $users->saveData();
    }
}
