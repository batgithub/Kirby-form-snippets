<!DOCTYPE html>
<html>
    <body>
        <h1>Nouvelle soumission au formulaire <?= $formName ?></h1>
        <p><?= $date ?></p>
        <table>
            <thead>
                <tr>
                    <th>Question</th>
                    <th>Réponse</th>
                </tr>
            </thead>
                <?php foreach($datas as $field => $data): ?>
                        <tr>
                            <td><?= $data['label'] ?></td>

                            <?php if($data['value'] !== ""): ?>
                                <td>
                                    <?php if($data['input'] == 'checkbox-group'): ?>
                                        <ul>
                                            <?php foreach( $data['value'] as $item): ?>
                                                <li><?= Str::replace($item, '-', ' ') ?></li>
                                            <?php endforeach ?>
                                        </ul>
        
                                    <?php else : ?>
                                        <?= $data['value'] ?>
                                    <?php endif; ?>
                                </td>
                            <?php else: ?>
                                <td>----</td>
                            <?php endif; ?>
                        </tr>            
                <?php endforeach ?>
        </table>
    </body>
</html>

