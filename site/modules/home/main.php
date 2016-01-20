<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 16/06/2015
 * Time: 9:17 PM
 */
class home extends Controller {
    public function index() {
        $user = CurrentUser::getUserSession();
        if(! $user->isLoggedIn()) {
            return new Response(200, "@home/notLoggedIn.twig", "Welcome", "home");
        }
        $hookEngine = HookEngine::getInstance();
        $timeline = new Timeline();
        $rawTimelineObjects = $hookEngine->runFilter("buildTimeline", array());
        $model = array(
            'user' => $user,
            'timeline' => $timeline
        );
        if($rawTimelineObjects === null) {
            NoticeEngine::getInstance()->addNotice(new Notice(noticeType::warning, "Sorry, no plugins were found to generate timeline content."));
            return new Response(200, "@home/main.twig", "Hi {$user->getFirstName()}", "home", $model);
        }
        $rawTimelineObjects = array_reduce($rawTimelineObjects, 'array_merge', array());
        foreach($rawTimelineObjects as $rawTimelineObject) {
            if(! ($rawTimelineObject instanceof ITimelineObject)) {
                continue;
            }
            $timeline->add($rawTimelineObject);
        }
        $model['timeline'] = $timeline;
        return new Response(200, "@home/main.twig", "Hi {$user->getFirstName()}", "home", $model);
    }
}