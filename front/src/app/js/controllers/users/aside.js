import { API_PREFIX } from 'upont/js/config/constants';

import './infos.html';
import './footer.html';

class Aside_Ctrl {
    constructor($scope, $rootScope, $resource, $http, $interval) {
        // CHARGEMENT DES DONNÉES DE BASE
        // Version de uPont
        $resource(API_PREFIX + 'version').get(function(data){
            $scope.version = data;
        });

        const loadAchievements = function() {
            $resource(API_PREFIX + 'own/achievements?all').get(function(data) {
                $scope.level = data.current_level;
            });
        };
        loadAchievements();

        $rootScope.$on('newAchievement', function() {
            loadAchievements();
        });

        $scope.toggleOpenState = function() {
            $http.patch(API_PREFIX + 'clubs/ki', {open: !$scope.open}).then(function(response){
                    $scope.open = response.data.open;
                });
        };
        // Gens en ligne
        const refreshData = () => {
            $resource(API_PREFIX + 'refresh').get(function(data){
                $scope.online = data.online;
                $scope.open = data.open;
            });
        };
        refreshData();
        $rootScope.updateInfo = $interval(refreshData, 60000);
    }
}

export default Aside_Ctrl;